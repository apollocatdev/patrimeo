# OpenAI ChatGPT Connector for Cotations

The OpenAI ChatGPT connector allows you to get real-time quotes for financial instruments using natural language prompts.

## Setup

1. Add your OpenAI API key to your `.env` file:
```env
OPENAI_API_KEY=your_openai_api_key_here
```

2. The connector is automatically available in the cotation update method dropdown as "OpenAI ChatGPT".

## Usage

### Basic Configuration

1. Create a new cotation or edit an existing one
2. Set the update method to "OpenAI ChatGPT"
3. Optionally customize the prompt in the "Custom prompt" field

### Custom Prompts

You can customize the prompt that will be sent to ChatGPT. Use `{cotation_name}` as a placeholder for the cotation name.

**Default prompt:** `Get the latest price of {cotation_name}`

**Example custom prompts:**
- `What is the current market price of {cotation_name} stock?`
- `Get me the latest quote for {cotation_name} cryptocurrency`
- `What's the current value of {cotation_name} bond?`

### How It Works

1. Your custom prompt (or the default) is processed, replacing `{cotation_name}` with the actual cotation name
2. The system automatically appends: "Answer just the price, with the currency of the cotation (EUR for Euro, USD for the dollar)"
3. The complete prompt is sent to OpenAI's GPT-3.5-turbo model
4. The response is parsed to extract the numeric price value
5. The price is returned as a float

### Example

If you have a cotation named "AAPL" with the default prompt:

**Final prompt sent to ChatGPT:**
```
Get the latest price of AAPL. Answer just the price, with the currency of the cotation (EUR for Euro, USD for the dollar).
```

**Expected response:** `$150.25` or `150.25 USD`

**Parsed result:** `150.25`

## Rate Limiting

The OpenAI connector uses the rate limiter key `openai` to prevent excessive API calls.

## Error Handling

The connector will throw a `CotationException` if:
- The OpenAI API key is not configured
- The API request fails
- The response cannot be parsed to a valid numeric value
- The price value is invalid (≤ 0)

## Cost Considerations

Each quote request costs approximately $0.0002 USD (using GPT-3.5-turbo). Consider your usage patterns and OpenAI billing settings.

## Security

- Your OpenAI API key is stored securely in environment variables
- API calls are made server-side and not exposed to the client
- Consider setting up API key restrictions in your OpenAI account for additional security 