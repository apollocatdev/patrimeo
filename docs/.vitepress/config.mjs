import { defineConfig } from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: "Patrimeo",
  description: "Open-source and selfhosted individual portfolio management",
  themeConfig: {
    // https://vitepress.dev/reference/default-theme-config
    logo: { light: '/images/patrimeo_horizontal_small.png', dark: '/images/patrimeo_horizontal_small_dark.png' },
    siteTitle: false,
    nav: [
      { text: 'Home', link: '/' },
      { text: 'Documentation', link: '/documentation' }
    ],

    sidebar: [
      {
        text: 'Introduction',
        items: [
          { text: 'Data Model', link: '/documentation/data-model' },
          { text: 'Runtime API Examples', link: '/api-examples' }
        ],
      },
      {
        text: 'Contributing',
        items: [
          { text: 'Introduction', link: '/documentation/contributing/introduction' },
        ],
      }
    ],

    socialLinks: [
      { icon: 'github', link: 'https://github.com/vuejs/vitepress' }
    ]
  }
})
