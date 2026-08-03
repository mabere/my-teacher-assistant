# RPP Structured Generation Technical Spike

## Status

Provider-neutral contract validated locally. No AI provider is called by this
spike.

## Purpose

Define a stable boundary between project context, an eventual AI provider,
database persistence, editor state, alignment checks, and document export.

## Input contract

`App\Data\Generation\RppGenerationInput` contains:

- project title, subject, grade, topic, and duration;
- learning outcomes and objectives;
- reusable class context;
- project-scoped source references.

The object serializes to a stable array through `toArray()` and is suitable
for a provider adapter or queued generation job.

## Output contract

`App\Data\Generation\RppGenerationOutput` requires:

- `identity` object;
- `learning_objectives` list;
- `learning_activities.opening`, `.core`, and `.closing` lists;
- `assessment.diagnostic`, `.formative`, and `.summative` lists.

Optional lists are `differentiation`, `remedial`, `enrichment`, and
`references`. Invalid provider output raises `InvalidArgumentException`; it
must not be persisted as a valid generated document.

## Sample payload

```json
{
  "identity": {
    "subject": "Bahasa Inggris",
    "grade": "VIII",
    "topic": "Descriptive Text",
    "duration_minutes": 80
  },
  "learning_objectives": ["Siswa dapat mendeskripsikan objek."],
  "learning_activities": {
    "opening": ["Apersepsi"],
    "core": ["Menganalisis contoh teks"],
    "closing": ["Refleksi"]
  },
  "assessment": {
    "diagnostic": [],
    "formative": ["Tanya jawab"],
    "summative": ["Tugas menulis"]
  },
  "differentiation": [],
  "remedial": [],
  "enrichment": [],
  "references": ["Buku Bahasa Inggris kelas VIII"]
}
```

## Acceptance criteria

1. Provider output is decoded as an array.
2. Required keys and list types are validated before persistence.
3. Invalid output produces a visible job failure and is not marked ready.
4. The normalized output can be serialized again without losing structure.
5. The same contract can be reused by the future LKPD generator and
   alignment checker.

## Next spike

Add a provider adapter that requests structured output against this contract,
records model/prompt versions and latency, then maps the validated output into
`GeneratedContent`. Keep the adapter behind an interface and do not put provider
calls in controllers.
