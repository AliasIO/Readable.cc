#!/usr/bin/ruby

require 'rubygems'
require 'curb'
require 'sanitize'
require 'mysql'

def get_words(item_url)
  c = Curl::Easy.new

  c.url = item_url

  c.perform

  body = c.body_str.downcase

  body.gsub!(/&[a-z]+;/, ' ')

  paragraphs = body.scan(/<(h[1-6]|p)[^>]*>([\s\S]+?)<\/\1>/i)

  content = ''

  paragraphs.each { |p| content += Sanitize.clean(p[1]) + ' ' }

  content.gsub!(/\W/i, ' ')
  content.gsub!(/\b([0-9]+|.)\b/i, ' ')

  words = content.split

  words_count = {}

  words.each { |word| words_count[word] ? words_count[word] += 1 : words_count[word] = 1 }

  return words, words_count
end

def analyze_items(item_urls, user_id)
  item_urls.each do |item_url|
    sql = '
      DELETE
        i, iw, ui
      FROM      items       AS  i
      LEFT JOIN items_words AS iw ON i.id = iw.item_id
      LEFT JOIN users_items AS ui ON i.id = ui.item_id
      WHERE
        i.url = "' + $db.escape_string(item_url) + '"
      ;'

    $db.query(sql)

    sql = '
      INSERT IGNORE INTO items (
        url,
        datetime
      ) VALUES (
        "' + $db.escape_string(item_url) + '",
        NOW()
      );'

    $db.query(sql)

    item_id = $db.insert_id

    if ( item_id )
      words, words_count = get_words(item_url)

      sql = '
        INSERT IGNORE INTO words (
          word
        ) VALUES (
          "' + words.join('" ), ( "') + '"
        );'

      $db.query(sql)

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

      $db.query(sql)

      sql = '
        INSERT IGNORE INTO users_items (
          user_id,
          item_id,
          vote
        ) VALUES (
          ' + user_id.to_s + ',
          ' + item_id.to_s + ',
          0
        );'

      $db.query(sql)

      puts 'Finished ' + item_url
    end
  end

  puts "Done\n"
end

# def upvote_word(word, user_id)
#   sql = '
#     UPDATE users_items SET
#       vote = 0
#     WHERE
#       user_id = ' + user_id.to_s + '
#     ;'
#
#   $db.query(sql)
#
#   sql = '
#     UPDATE    users_items AS ui
#     LEFT JOIN items_words AS iw ON ui.item_id = iw.item_id
#     LEFT JOIN words       AS  w ON iw.word_id =  w.id
#     SET
#       vote = 1
#     WHERE
#       ui.user_id = ' + user_id.to_s + ' AND
#        w.word    = "' + $db.escape_string(word) + '"
#     ;'
#
#   $db.query(sql)
# end

$db = Mysql.new('localhost', 'root', '', 'readable_cc')

user_id = 1

test_data = YAML.load_file('test_data.yml')

item_urls = test_data['urls_like'] | test_data['urls_neutral'] | test_data['urls_dislike']

sql = '
  DELETE
  FROM users_items
  WHERE
    user_id = ' + user_id.to_s + '
  ;'

$db.query(sql)

analyze_items(item_urls, user_id)

# upvote_word('python', user_id)

item_urls.map do |url|
  vote = test_data['urls_like'].include?(url) ? 1 : ( test_data['urls_dislike'].include?(url) ? -1 : 0 )

  sql = '
    UPDATE    users_items AS ui
    LEFT JOIN items       AS  i ON ui.item_id = i.id SET
      ui.vote = ' + vote.to_s + '
    WHERE
      i.url = "' + db.escape_string(url) + '"
    LIMIT 1
    ;'

  $db.query(sql)
end

sql = '
  DELETE
  FROM users_words
  WHERE
    user_id = ' + user_id.to_s + '
  ;'

$db.query(sql)

sql = '
  INSERT INTO users_words (
    user_id,
    word_id,
    score
  )
  SELECT
    main.user_id                     AS user_id,
    main.word_id                     AS word_id,
    main.vote * ( @row := @row + 1 ) AS score
  FROM (
    SELECT
      w.id         AS word_id,
      ui.user_id   AS user_id,
      SUM(ui.vote) AS vote
    FROM      words       AS  w
    LEFT JOIN items_words AS iw ON  w.id      = iw.word_id
    LEFT JOIN items       AS  i ON iw.item_id =  i.id
    LEFT JOIN users_items AS ui ON  i.id      = ui.item_id
    WHERE
      ui.user_id = ' + user_id.to_s + '
    GROUP BY w.id
    ORDER BY count DESC
  ) AS main, (
    SELECT @row := 0
  ) AS rownum
  ;'

$db.query(sql)

sql = '
  SELECT
    i.url,
    SUM(uw.score) AS score
  FROM      items       AS  i
  LEFT JOIN items_words AS iw ON  i.id      = iw.item_id
  LEFT JOIN users_words AS uw ON iw.word_id = uw.word_id
  WHERE
    uw.user_id = ' + user_id.to_s + '
  GROUP BY i.id
  ORDER BY
    score DESC
  ;'

result = $db.query(sql)

while row = result.fetch_hash do
  puts row.inspect
end
