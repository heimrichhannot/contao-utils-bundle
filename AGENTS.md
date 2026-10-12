- Read and follow `AGENTS.local.md` in the repository root if it exists for additional local instructions.
- Read and follow `~/Kunden/github/contao-skeleton-bundle` if it exists for latest company-wide instructions.

## Coding standards
- Do not over-engineer!
- Use Symfony PHP Translation Format for Contao and Other Translations where possible when creating new files.
- Omit explicit `label` entries in Contao DCA field definitions. Contao resolves field labels automatically from the table's translations; define the labels and help text in the Symfony PHP translation files.
- Use Doctrine Schema Representation for Contao DCA SQL Column Definition.
- Do not define a custom targetColumn for Contao DCA virtual fields unless explicitly requested.
- Use AbstractBundle class for the bundle class, if possible
- Register listeners with PHP attributes, never in configuration. Use `#[AsCallback]` for
  Contao DCA callbacks, `#[AsHook]` for Contao hooks, `#[AsEventListener]` for Symfony
  events, `#[AsCronJob]` for cronjobs, and the matching Contao attributes for content
  elements, frontend modules and insert tags. Do not add callbacks to DCA arrays
  (`'onload_callback' => [...]`), do not add hooks to `$GLOBALS['TL_HOOKS']`, and do not add
  `contao.callback` / `contao.hook` / `kernel.event_listener` tags to `services.yaml`.
  The attribute keeps the registration next to the code it registers.
- When several listeners share one callback target and their order matters: set an explicit
  `priority` on each attribute instead of relying on declaration order.
- Avoid trivial wrapper methods that only delegate to another method or wrap a simple expression without adding meaningful behavior. Keep such calls inline when the wrapper merely renames the underlying operation. Extract a method when it encapsulates meaningful logic, removes substantive duplication, or establishes a necessary extension or integration point. A descriptive name alone does not justify a one-line wrapper.
- Do not give your own concepts Contao-reserved names. For example the class suffix `*Model`and the directory `src/Model/` belong to Contao Active Record classes (`Contao\Model` subclasses registered in `$GLOBALS['TL_MODELS']`).
- Create Content Elements, not Frontend Module (Frontend Modules are a deprecated concept in contao)
- Derive palettes for content elements (and other core tables) from the core DCA of the installed Contao version, e.g. the `headline` palette in `vendor/contao/core-bundle/contao/dca/tl_content.php`. Do not copy palettes from existing elements in the project or other bundles; they may still contain fields removed in Contao 5 (`guests`, `cssClass`). Every field in a palette or subpalette must exist in the table's `fields`.
- Do not create .html5 templates if not explicit required. Always create Twig templates in the contao managed namespace.
- In Contao Twig templates, build HTML attributes with the `attrs()` Twig function (`Contao\CoreBundle\String\HtmlAttributes`) instead of hand-written attribute strings whenever an element has more than one static class, data or ARIA attributes, dynamic values, or conditional attributes. Chain `.addClass()`, `.set(name, value, condition)`, `.setIfExists()` and `.mergeWith()`; set boolean attributes with `.set('hidden')`, pass JSON with `.set('data-x', value|json_encode)`, and let `attrs()` handle escaping (do not add `|e` or `|raw`). When a template receives attributes, start from them: `attrs(attributes|default)` or `attrs(cssID)`. For repeated elements, define a base once (`{% set button = attrs().set('type', 'button').addClass('btn') %}`) and derive each element with `attrs(button).set(...)`; `attrs()` copies, so the base stays unchanged. Elements with a single static class and no other attributes may stay plain HTML. In PHP, pass `HtmlAttributes` objects to templates instead of prebuilt attribute strings.

## Structure
- `contao/templates/`
    Store all templates here, also twig (not in symfony templates folder). If the extension has .html5 templates, use the `contao/templates/twig` folder for putting twig templates. The twig root folder must contain a `.twig-root` file. Twig-Templates within that folder can be addressed with the `@Contao` twig namespace. 
- `src/EventListener/Cron/`
    Cronjobs
- `src/EventListener/DataContainer/[Table]/`
    DCA Callback Listener. One Class per Callback. [Table ] is the table name without tl_ prefix and CamelCalse, for example Member for tl_member. Name the classes after the callback name with Listener suffix, for example ConfigOnLoadListener for 'config.onload' or FieldsExampleOptionsListener for 'fields.example.options'
- `src/Model/`
  Contao Active Record classes only. One class per table, named `<Table>Model`, extending `Contao\Model`, registered in `$GLOBALS['TL_MODELS']`.


## Third-party APIs

- Before using any class, interface, method or constant from `vendor/`, open the
  file and look for `@deprecated` annotations and `trigger_deprecation()` calls.
  If it is deprecated, use the successor named in the deprecation message.
- An instruction that names a specific API does not override this. Prompts,
  issues and specifications describe intent, not the exact symbol to use. If the
  named API turns out to be deprecated, internal or experimental, use the
  documented successor and report the substitution in the summary of your work,
  not only in a decisions table. If there is no successor, or switching would
  change behaviour, stop and ask instead of implementing the deprecated path.
- Name the version that deprecated something and the version that removes it, so
  the reader can judge urgency.
- Never silence deprecation warnings in tests or tooling. A run that emits
  deprecations caused by this bundle's own code is a failing run.

## Tests must exercise real behaviour

- A test that replaces a framework service with a stub proves only that your code
  calls it. Where the framework does non-trivial work on your input (formatting,
  escaping, translation, routing, serialisation), at least one test must run
  through the real implementation.
- Registration is not rendering. Proving that a callback, module or service is
  registered says nothing about whether it produces valid output. Cover the
  output as well.

## Changelog

- Orientiere dich an [Keep a Changelog](https://keepachangelog.com/de/), verwende jedoch keine separaten Überschriften für Änderungsarten.
- Führe unveröffentlichte Änderungen unter `## [Unreleased]` ganz oben.
- Verwende für Releases `## [Versionsnummer] - YYYY-MM-DD`, neueste Version zuerst.
- Schreibe jede Änderung als Listenpunkt mit Kategorie und Doppelpunkt, z. B. `- Fixed: Beschreibung`.
- Kategorien: `Added` (neue Features), `Changed` (bestehende Funktionalität), `Deprecated` (zur Entfernung vorgesehen), `Removed` (entfernte Features), `Fixed` (Bugfixes), `Security` (geschlossene Sicherheitslücken).
- Überführe beim Release die unveröffentlichten Einträge in den neuen Versionsabschnitt und behalte darüber einen leeren Abschnitt `## [Unreleased]`.
- Beschreibe die Änderung aus Sicht des Nutzers oder Integrators, nicht ihre Umsetzung. Ein Eintrag ist in der Regel ein Satz.
- Lass Klassennamen, CSS-Eigenschaften, Methoden, Dateinamen, Ursachenanalyse und Lösungsweg weg. Das gehört in die Dokumentation und in die Commit-Message.
- Nenne technische Bezeichner nur, wenn der Leser sie selbst verwenden muss, etwa bei einer umbenannten Konfigurationsoption oder einem Breaking Change.
