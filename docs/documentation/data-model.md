# Data Model

Patrimeo is built around a core concept of **Assets** and **Cotations**, where each asset is associated with one cotation to track its current market value. This foundation is extended with additional features to provide comprehensive portfolio management capabilities.

## Core Concepts

### Assets
Assets represent the financial instruments in your portfolio. Each asset has:
- **Name**: A descriptive name for the asset
- **Quantity**: How many units you own
- **Value**: Current market value (calculated from quantity × cotation price)
- **Asset Class**: Categorization (e.g., Stocks, Bonds, Crypto, etc.)
- **Envelope**: The account or container where the asset is held
- **Taxonomies**: Custom labels for organization and filtering

### Cotations
Cotations track the current market price of financial instruments. Each cotation includes:
- **Name**: The instrument identifier (e.g., stock symbol)
- **ISIN**: International Securities Identification Number
- **Currency**: The currency in which the price is quoted
- **Value**: Current market price
- **Update Method**: How the price is retrieved (Yahoo Finance, manual, etc.)
- **Last Update**: When the price was last refreshed

The relationship between Assets and Cotations is one-to-many: one cotation can be used by multiple assets, but each asset is linked to exactly one cotation.

## How Assets and Cotations are Updated

Both Assets and Cotations have update mechanisms to keep their values current:

### Cotation Updates
Cotations can be updated using various methods:
- **Yahoo Finance**: Automatic price fetching from Yahoo Finance
- **XPath**: Custom web scraping using XPath selectors
- **CSS Selectors**: Web scraping using CSS selectors
- **Coingecko**: Cryptocurrency price data from Coingecko
- **OpenAI**: AI-powered price extraction from web content
- **Manual**: User-entered prices
- **Fixed**: Static prices that don't change
- **Command**: Custom command execution for price updates

### Asset Updates
Assets can be updated through:
- **Automatic Updates**: When their associated cotation price changes
- **Transfer Updates**: When transfers modify asset quantities
- **Manual Updates**: Direct user modifications
- **Scheduled Updates**: Automated updates via schedules

Both Assets and Cotations track their **Last Update** timestamp to ensure data freshness and can be configured with different update methods based on the type of financial instrument and available data sources.

## Organizational Features

### Asset Classes
Asset classes help categorize your investments by type:
- **Stocks**: Individual company shares
- **Bonds**: Government or corporate debt instruments
- **Cryptocurrencies**: Digital currencies
- **ETFs**: Exchange-traded funds
- **Mutual Funds**: Professionally managed investment funds
- **Real Estate**: Property investments
- **Commodities**: Physical goods like gold, oil, etc.

### Envelopes
Envelopes represent the accounts or containers where your assets are held:
- **Bank Accounts**: Traditional savings and checking accounts
- **Brokerage Accounts**: Investment platforms
- **Retirement Accounts**: 401(k), IRA, etc.
- **Crypto Wallets**: Digital currency storage
- **Physical Holdings**: Cash, precious metals, etc.

### Taxonomies
Taxonomies provide flexible labeling systems for organizing your portfolio:
- **Asset Taxonomies**: Labels for categorizing assets (e.g., "Growth", "Value", "Tech Sector")
- **Transfer Taxonomies**: Labels for categorizing transactions (e.g., "Salary", "Investment", "Emergency Fund")

Taxonomies can be weighted or unweighted, allowing for different levels of importance in your organization system.

## Transaction Management

### Transfers
Transfers represent all financial movements in your portfolio:
- **Income**: Money coming into your portfolio (salary, dividends, etc.)
- **Expenses**: Money leaving your portfolio (purchases, fees, etc.)
- **Transfers**: Moving money between assets or accounts

Each transfer includes:
- **Source and Destination**: Which assets or accounts are involved
- **Quantities**: How much is being moved
- **Date**: When the transaction occurred
- **Comment**: Additional notes about the transaction
- **Taxonomies**: Categorization for reporting and analysis

## Automation and Scheduling

### Schedules
Schedules automate repetitive tasks in your portfolio management:
- **Price Updates**: Automatically refresh cotation prices
- **Asset Updates**: Update asset quantities or values
- **Report Generation**: Generate periodic reports
- **Notifications**: Send alerts about portfolio changes

Schedules use cron expressions to define when tasks should run, and can be applied to specific assets or cotations.

## Filtering and Analysis

### Filters
Filters allow you to create custom views of your portfolio:
- **Asset Filters**: Show only assets matching certain criteria
- **Transfer Filters**: Focus on specific types of transactions
- **Date Ranges**: Analyze performance over specific periods
- **Value Ranges**: Focus on assets within certain value ranges
- **Taxonomy Filters**: Show assets with specific tags

Filters can be combined using AND/OR logic to create complex selection criteria.

## Data Relationships

The data model is designed with clear relationships:

1. **Users** own all their data (Assets, Cotations, Transfers, etc.)
2. **Assets** belong to one **Asset Class** and one **Envelope**
3. **Assets** are linked to one **Cotation** for price tracking
4. **Assets** can have multiple **Taxonomies** for organization
5. **Transfers** connect **Assets** (source and destination)
6. **Transfers** can have **Taxonomies** for categorization
7. **Schedules** can be applied to **Assets** or **Cotations**
8. **Filters** can be applied to **Widgets** for dashboard customization

This structure provides a flexible foundation for portfolio management while maintaining data integrity and user privacy through proper scoping and relationships.
