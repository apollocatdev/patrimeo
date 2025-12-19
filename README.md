
![Version](https://img.shields.io/badge/version-0.2.2--alpha-orange)

![Logo clair](public/images/patrimeo_vertical.png#gh-light-mode-only)
![Logo sombre](public/images/patrimeo_vertical_dark.png#gh-dark-mode-only)


**Patrimeo is a powerful open source self-hosted financial portfolio management application**

Take control of your financial portfolio with Patrimeo's comprehensive suite of tools designed for modern investors. Whether you're managing stocks, bonds, cryptocurrencies, or cash accounts, Patrimeo provides real-time tracking, advanced analytics, and automated data synchronization to keep your portfolio insights up-to-date.

## Features

### 📊 **Portfolio Management**

- **Multi-Asset Support**: Track stocks, bonds, cryptocurrencies, cash accounts, and more
- **Real-time Valuations**: Automatic price updates with multiple data sources
- **Asset Organization**: Organize assets by envelopes (accounts) and asset classes
- **Transaction Tracking**: Monitor income, expenses, and transactions between assets
- **Historical Data**: Complete transaction history and portfolio evolution tracking

### 📈 **Advanced Analytics & Dashboards**
- **Customizable Dashboards**: Create personalized dashboards with drag-and-drop widgets
- **Performance Metrics**: Time-Weighted Return (TWR) and Money-Weighted Return (MWR) calculations
- **Visual Charts**: Line charts, donut charts, treemaps, and bar charts for portfolio analysis
- **Asset Distribution**: Visual breakdown by asset class, envelope type, and custom taxonomies
- **Portfolio Statistics**: Total value, gains/losses, and performance tracking

### 🔄 **Data Import & Synchronization**
- **Flexible Import System**: Import data from various sources including Finary (beta)
- **Automated Updates**: Scheduled price updates with configurable schedules
- **Multiple Data Sources**: Support for various APIs and data providers
- **Update data through external scripts**: Integrate command-line tools external tools or scripts to update assets or prices


### 🏷️ **Organization & Filtering**
- **Taxonomy System**: Create custom tags and categories for assets
- **Advanced Filtering**: Filter assets by class, envelope, value, quantity, and more
- **Multi-dimensional Views**: Analyze portfolio by different dimensions
- **Custom Asset Classes**: Define your own asset classification system

### 🔔 **Notifications & Scheduling**
- **Smart Notifications**: Get notified about portfolio updates, market alerts, and system events
- **Scheduled Tasks**: Automate data synchronization with cron-based scheduling
- **Email Notifications**: Receive portfolio summaries and alerts via email
- **Real-time Updates**: Stay informed with in-app notification system


## Synchronization modules

The following synchronization modules are available. We plan to add more modules in the near future.

### Price updates

| Name | Description |
|------|-------------|
| CoinGecko | Real-time cryptocurrency price updates via CoinGecko API |
| Command | Execute custom command-line scripts for price updates |
| CSS | Extract price data using CSS selectors from web pages |
| OpenAI ChatGPT | Use ChatGPT for real-time price queries via natural language |
| XPath | Extract price data using XPath expressions from web pages |
| XPathJavascript | Extract price data using XPath with JavaScript execution |
| Yahoo | Real-time price updates via Yahoo Finance API |

### Asset updates

| Name | Description |
|------|-------------|
| CommandJson | Execute command-line scripts that return JSON data for asset updates |
| CommandSimpleBalance | Execute command-line scripts for simple balance updates |
| Finary | Update asset balance from [Finary](https://finary.com) |
| Woob | Import data from various French banks and financial institutions via [Woob](https://woob.tech) |
