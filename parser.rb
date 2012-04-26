#!/usr/bin/ruby

require 'rubygems'
require 'curb'
require 'sanitize'
require 'mysql'

module Readable
  @@c      = Curl::Easy.new
  @@config = YAML.load_file('config.yml')
  @@db     = Mysql.new(@@config['db']['host'], @@config['db']['user'], @@config['db']['pass'], @@config['db']['name'])

  def Readable.analyze_items(item_urls)
    item_urls.each do |item_url|
      sql = '
        DELETE
          i, iw, ei
        FROM      items          AS  i
        LEFT JOIN items_words    AS iw ON i.id = iw.item_id
        LEFT JOIN entities_items AS ei ON i.id = ei.item_id
        WHERE
          i.url = "' + @@db.escape_string(item_url) + '"
        ;'

      @@db.query(sql)

      sql = '
        INSERT IGNORE INTO items (
          url,
          datetime
        ) VALUES (
          "' + @@db.escape_string(item_url) + '",
          NOW()
        );'

      @@db.query(sql)

      item_id = @@db.insert_id

      if ( item_id )
        words, words_count = Readable.extract_words(item_url)

        next if words.empty?

        sql = '
          INSERT IGNORE INTO words (
            word
          ) VALUES (
            "' + words.join('" ), ( "') + '"
          );'

        @@db.query(sql)

        inserts = []

        words_count.each do |word, count|
          inserts.push(item_id.to_s + ', ( SELECT id FROM words WHERE word = "' + word + '" LIMIT 1 ), ' + count.to_s)
        end

        sql = '
          INSERT IGNORE INTO items_words (
            item_id,
            word_id,
            count
          ) VALUES (
            ' + inserts.join(' ), ( ') + '
          );'

        @@db.query(sql)

        puts 'Finished ' + item_url
      end
    end

    puts "Done\n"
  end

  def Readable.extract_words(item_url)
    @@c.url = item_url

    begin
      @@c.connect_timeout = 3
      @@c.perform

      body = @@c.body_str.downcase
    rescue
      puts 'Failed ' + item_url

      return [], {}
    end

    paragraphs = body.gsub(/&[a-z]+;/, ' ').scan(/<(h[1-6]|p)[^>]*>([\s\S]+?)<\/\1>/i)

    content = ''

    paragraphs.each { |p| content += Sanitize.clean(p[1]) + ' ' }

    content.gsub!(/\W/i, ' ')
    content.gsub!(/\b([0-9]+|.)\b/i, ' ')

    words = content.split

    words_count = {}

    words.each { |word| words_count[word] ? words_count[word] += 1 : words_count[word] = 1 }

    return words, words_count
  end

  def Readable.rank_words(entity_id)
    sql = '
      INSERT INTO entities_words (
        entity_id,
        word_id,
        score
      )
      SELECT
        main.entity_id                   AS entity_id,
        main.word_id                     AS word_id,
        main.vote * ( @row := @row + 1 ) AS score
      FROM (
        SELECT
           w.id        AS word_id,
          ei.entity_id AS entity_id,
          SUM(ei.vote) AS vote
        FROM      words          AS  w
        LEFT JOIN items_words    AS iw ON  w.id      = iw.word_id
        LEFT JOIN items          AS  i ON iw.item_id =  i.id
        LEFT JOIN entities_items AS ei ON  i.id      = ei.item_id
        WHERE
          ei.entity_id = ' + entity_id.to_s + '
        GROUP BY w.id
        ORDER BY count DESC
      ) AS main, (
        SELECT @row := 0
      ) AS rownum
      ;'

    @@db.query(sql)
  end

  def Readable.get_top_items(entity_id)
    sql = '
      SELECT
        i.url,
        SUM(ew.score) AS score
      FROM      items          AS  i
      LEFT JOIN items_words    AS iw ON  i.id      = iw.item_id
      LEFT JOIN entities_words AS ew ON iw.word_id = ew.word_id
      LEFT JOIN entities_items AS ei ON  i.id      = ei.item_id
      WHERE
        ew.entity_id = ' + entity_id.to_s + ' AND
        ei.item_id   IS NULL
      GROUP BY i.id
      ORDER BY
        score DESC
      ;'

    result = @@db.query(sql)

    items = []

    while row = result.fetch_hash
      items += [ row ]
    end

    return items
  end

  def Readable.entity_clear(entity_id)
    sql = '
      DELETE
      FROM entities_items
      WHERE
        entity_id = ' + entity_id.to_s + '
      ;'

    @@db.query(sql)

    sql = '
      DELETE
      FROM entities_words
      WHERE
        entity_id = ' + entity_id.to_s + '
      ;'

    @@db.query(sql)
  end

  def Readable.vote(entity_id, item_url, vote)
    sql = '
      INSERT IGNORE INTO entities_items (
        entity_id,
        item_id,
        vote
      ) VALUES (
        ' + entity_id.to_s + ',
        ( SELECT id FROM items WHERE url = "' + @@db.escape_string(item_url) + '" ),
        ' + vote.to_s + '
      )
      ON DUPLICATE KEY UPDATE
        vote = ' + vote.to_s + '
      ;'

    @@db.query(sql)
  end
end

entity_id = 1

Readable.entity_clear(entity_id)

test_data = YAML.load_file('test_data.yml')

item_urls = test_data['urls_like'] | test_data['urls_dislike'] | test_data['urls_unrated']

Readable.analyze_items(item_urls)

test_data['urls_like'   ].each { |url| Readable.vote(entity_id, url,  1) }
test_data['urls_dislike'].each { |url| Readable.vote(entity_id, url, -1) }

Readable.rank_words(entity_id)

puts "\n" + 'Unread items in order of relavance:'

items = Readable.get_top_items(entity_id)

items.each { |item| puts item['url'] }
