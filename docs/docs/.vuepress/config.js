module.exports = {
    title: 'Social Documentation',
    description: 'Social Documentation',
    base: '/docs/social/',
    plugins: {
        '@vuepress/google-analytics': {
            'ga': 'UA-1547168-20'
        },
        'sitemap': {
            hostname: 'https://dukt.net/docs/social/'
        },
    },
    theme: 'default-prefers-color-scheme',
    themeConfig: {
        docsRepo: 'dukt/social-docs',
        docsDir: 'docs',
        docsBranch: 'v2',
        editLinks: true,
        editLinkText: 'Edit this page on GitHub',
        lastUpdated: 'Last Updated',
        sidebar: {
         '/': [
             {
                 title: 'Social plugin for Craft CMS',
                 collapsable: false,
                 children: [
                     '',
                     'requirements',
                     'installation',
                     'updating',
                     'configuration',
                 ]
             },
             {
                 title: 'Login Providers',
                 collapsable: false,
                 children: [
                     'login-providers',
                     'facebook',
                     'google',
                     'twitter',
                 ]
             },
             {
                 title: 'Usage',
                 collapsable: false,
                 children: [
                     'registration',
                     'login',
                     'logout',
                     'managing-login-accounts',
                     'notice-error-messages',
                     'demo-templates',
                 ]
             }
         ],
        }
    }
}
