module.exports = {
	base: '/shortlist/1.x/',
	title: 'Shortlist Documentation',
	description: 'Lightweight, flexible lists for Craft CMS.',
	theme: 'craftdocs',
	themeConfig: {
		docsRepo: 'TopShelfCraft/Shortlist',
		docsDir: 'docs',
		docsBranch: '2.x.dev',
		editLinks: true,
		editLinkText: 'Edit this page on GitHub',
		lastUpdated: 'Last Updated',
		repo: 'TopShelfCraft/Shortlist',
		repoLabel: 'Shortlist on GitHub',
		nav: [
			{
				text: 'Version',
				items: [
					// {
					// 	text: '3.x Guide',
					// 	link: 'https://docs.topshelfcraft.com/shortlist/3.x/'
					// },
					// {
					// 	text: '3.x API',
					// 	link: 'https://docs.topshelfcraft.com/shortlist/3.x/api'
					// },
					{
						text: '1.x Guide',
						link: '/'
					},
					{
						text: '1.x API',
						link: 'https://docs.topshelfcraft.com/shortlist/1.x/api'
					},
				]
			}
		],
		sidebar: {
			'/': [
				{
					title: 'Guide',
					collapsable: false,
					children: [
						['', 'Introduction'],
						'installation',
						'working-with-items',
						'working-with-lists',
						'more-on-actions',
						'handling-errors',
						'faq'
					]
				}
			]
		},
		sidebarDepth: 3
	},
	markdown: {
		anchor: { level: [2, 3] },
		config: md => {
			md.use(require('vuepress-theme-craftdocs/markup'))
			  .use(require('markdown-it-deflist'));
		}
	}
};