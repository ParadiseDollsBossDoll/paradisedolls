# Google Translation Cost Explanation

This website uses Google Cloud Translation API for the language translator.

Google does not charge based on how many people visit the website. It charges based on how much text is sent to Google for translation. This is measured in characters, meaning letters, numbers, spaces, and punctuation.

## Current Google Pricing

Google's current pricing for standard text translation is:

- First 500,000 translated characters per month: free
- After 500,000 characters: $20 USD per 1,000,000 characters

This means the translator is usage-based, not a fixed monthly subscription.

Official Google sources:

- https://cloud.google.com/translate/pricing
- https://docs.cloud.google.com/translate/quotas

## How This Applies To Paradise Dolls

The website translation system is built with caching.

Caching means that when a piece of text is translated once, the website saves that translated result for a period of time. If another visitor asks for the same text in the same language while it is still cached, the website can reuse the saved translation instead of sending the same text to Google again.

Example:

- A visitor translates the homepage to Portuguese.
- Google translates the homepage text once.
- The website saves that Portuguese translation.
- More visitors choosing Portuguese can reuse the saved translation.
- Google usually does not charge again for the exact same cached text.

So the cost is not simply:

```text
more visitors = more cost
```

It is more accurate to say:

```text
more unique text translated into more languages = more cost
```

## What Can Increase The Cost

The translation cost can increase when:

- Visitors choose a language that has not been translated before.
- A page contains text that has not been translated before.
- Website text is changed or new text is added.
- The translation cache expires or is cleared.
- Many different languages are used heavily.

## What Does Not Usually Increase The Cost Much

The cost should not increase much when:

- Many visitors view the website in English.
- Many visitors choose the same translated language after it has already been cached.
- Visitors refresh or revisit pages that already have cached translations.

## Estimated Cost For This Website

Based on the current public website content, translating the main public pages into Thai and Portuguese should likely stay inside Google's free monthly allowance at the beginning.

Expected cost for normal early usage:

```text
$0/month or very low
```

Example Google cost ranges:

| Monthly translated characters | Estimated cost |
| --- | ---: |
| Up to 500,000 characters | $0 |
| 1,000,000 characters | About $10 |
| 2,000,000 characters | About $30 |
| 10,000,000 characters | About $190 |

These are estimates. The actual final amount can only be confirmed from the Google Cloud billing and usage dashboard after real visitors start using the translator.

## Safety Recommendation

To avoid unexpected charges, it is recommended to set a Google Cloud budget alert, for example $5 or $10.

This does not change the website itself. It is a setting inside Google Cloud Billing that sends a warning if spending starts to reach the chosen amount.

The website also has a cache setting named `TRANSLATION_CACHE_TTL`. A longer cache time can reduce repeat translation calls. For example:

- `604800` seconds = 7 days
- `2592000` seconds = 30 days

Using a longer cache can help keep translation costs lower because the same translated text can be reused for longer.

## Simple Client Summary

The translation cost is based on the amount of website text translated, not directly on the number of visitors. Because the website saves translated text in a cache, repeated visitors using the same language usually do not keep creating the same translation cost again. For the current website size and expected use, the cost should likely be free or very low at first, especially with Thai and Portuguese as the main translated languages.
