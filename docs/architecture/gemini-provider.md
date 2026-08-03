# Gemini Provider Baseline

## Decision

Gemini is the first provider for the local Functional Alpha trial. The
application still uses `RppGenerator` as an internal contract so another
provider can be added without changing controllers or jobs.

The active default model is `gemini-3.6-flash`. The previous
`gemini-2.5-flash` value is migrated because it is no longer available to new
Gemini API users.

## Configuration sources

The preferred configuration path is the admin-only page:

`/admin/ai-provider`

The provider setting stores the model, base URL, active state, last connection
test result, and encrypted API key in `ai_provider_settings`. The browser only
receives a masked hint; plaintext is never rendered back.

Environment variables remain a fallback for local bootstrap and automated
tests:

```dotenv
AI_PROVIDER=gemini
GEMINI_API_KEY=your-key
GEMINI_MODEL=gemini-3.6-flash
GEMINI_BASE_URL=https://generativelanguage.googleapis.com/v1beta
GEMINI_TIMEOUT=90
```

## Request boundary

`GeminiRppGenerator` calls `generateContent` and requests JSON structured output
using the RPP schema. The response is normalized by `RppGenerationOutput` before
it can be persisted.

## Current status

- Model migration from `gemini-2.5-flash` to `gemini-3.6-flash` is included.
- Admin provider settings and encrypted key storage are active.
- Local structured-output tests use HTTP fakes and do not consume API quota.
- Live generation must still be verified with the configured project.

Official references:

- [Gemini models](https://ai.google.dev/gemini-api/docs/models)
- [Gemini deprecations](https://ai.google.dev/gemini-api/docs/deprecations?hl=en)
- [Gemini Generate Content](https://ai.google.dev/api/generate-content?hl=en)
