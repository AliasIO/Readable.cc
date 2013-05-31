MathJax.Hub.Config({
	config: [],
	styleSheets: [],
	styles: {},
	jax: ['input/TeX', 'output/HTML-CSS'],
	extensions: ['tex2jax.js'],
	preJax: null,
	postJax: null,
	preRemoveClass: 'MathJax_Preview',
	showProcessingMessages: false,
	messageStyle: 'none',
	displayAlign: 'left',
	displayIndent: '2em',
	delayStartupUntil: 'none',
	skipStartupTypeset: false,
	tex2jax: {
		element: null,
		inlineMath: [
			['[itex]', '[/itex]'],
			['##', '##']
			],
		displayMath: [
			['[tex]', '[/tex]'],
			['$$', '$$']
			],
		skipTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code'],
		ignoreClass: 'tex2jax_ignore',
		processClass: 'tex2jax_process',
		processEscapes: false,
		processEnvironments: true,
		preview: 'TeX'
	},
	mml2jax: {
		element: null,
		preview: 'alttext'
	},
	jsMath2jax: {
		element: null,
		preview: 'TeX'
	},
	TeX: {
		TagSide: 'right',
		TagIndent: '.8em',
		MultLineWidth: '85%',
		Macros: {}
	},
	MathML: {
		useMathMLspacing: false
	},
	'HTML-CSS': {
		scale: 110,
		availableFonts: ['STIX', 'TeX'],
		preferredFont: 'TeX',
		webFont: 'TeX',
		imageFont: null,
		showMathMenu: false,
		styles: {},
		tooltip: {
			delayPost: 600,
			delayClear: 600,
			offsetX: 10,
			offsetY: 5
		}
	},
	NativeMML: {
		scale: 110,
		showMathMenu: true,
		showMathMenuMSIE: true,
		styles: {}
	},
	MMLorHTML: {
		prefer: {
			MSIE: 'MML',
			Firefox: 'MMLHTML',
			Opera: 'HTML',
			other: 'HTML'
		}
	}
});

MathJax.Ajax.loadComplete('readable.js');
