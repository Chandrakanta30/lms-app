<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HtmlIntermediateDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('master_documents')->updateOrInsert(
            ['doc_number' => 'HTML-INT-001'],
            [
                'doc_name' => 'HTML Intermediate Question Bank',
                'version' => '1.0',
                'doc_type' => 'Others',
                'file_path' => 'master_docs/html-intermediate-question-bank.html',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $documentId = DB::table('master_documents')
            ->where('doc_number', 'HTML-INT-001')
            ->value('id');

        DB::table('master_questions')
            ->where('master_document_id', $documentId)
            ->delete();

        $questionRows = array_map(
            function (array $question) use ($documentId, $now) {
                return [
                    'master_document_id' => $documentId,
                    'question_text' => $question['question_text'],
                    'question_type' => $question['question_type'],
                    'options' => $question['options'] ? json_encode($question['options']) : null,
                    'correct_answer' => $question['correct_answer'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            },
            $this->buildQuestions()
        );

        foreach (array_chunk($questionRows, 100) as $chunk) {
            DB::table('master_questions')->insert($chunk);
        }
    }

    private function buildQuestions(): array
    {
        $questions = [];

        foreach (array_slice($this->yesNoPairs(), 0, 50) as [$trueStatement, $falseStatement]) {
            $questions[] = [
                'question_text' => $trueStatement,
                'question_type' => 'yes_no',
                'correct_answer' => 'Yes',
                'options' => null,
            ];

            $questions[] = [
                'question_text' => $falseStatement,
                'question_type' => 'yes_no',
                'correct_answer' => 'No',
                'options' => null,
            ];
        }

        foreach (array_slice($this->mcqs(), 0, 100) as [$questionText, $options, $correctAnswer]) {
            $questions[] = [
                'question_text' => $questionText,
                'question_type' => 'mcq',
                'correct_answer' => $correctAnswer,
                'options' => $options,
            ];
        }

        if (count($questions) !== 200) {
            throw new \RuntimeException('Expected exactly 200 HTML questions, found ' . count($questions) . '.');
        }

        return $questions;
    }

    private function yesNoPairs(): array
    {
        return [
            ['The `<main>` element should appear only once per HTML document.', 'A valid HTML document should contain multiple `<main>` elements for each content section.'],
            ['The `alt` attribute on an informative `<img>` should describe the image purpose.', 'The `alt` attribute should always repeat the file name of the image.'],
            ['The `<label>` element can improve usability when associated with a form control.', 'A `<label>` element only changes visual styling and has no form usability benefit.'],
            ['The `<button>` element defaults to type `submit` inside a form if no type is specified.', 'A `<button>` inside a form defaults to type `button` unless explicitly changed.'],
            ['The `<section>` element is typically used for thematic grouping of content.', 'The `<section>` element is intended only for navigation links.'],
            ['The `<article>` element is appropriate for self-contained content that could stand on its own.', 'The `<article>` element should only be used inside tables.'],
            ['The `<aside>` element is commonly used for tangential or complementary content.', 'The `<aside>` element is the correct element for the page’s primary content block.'],
            ['The `<header>` element can be used inside a page or inside a sectioning element.', 'The `<header>` element may only appear once in the entire document.'],
            ['The `<footer>` element can be used for a page or for an individual section.', 'The `<footer>` element is only valid at the bottom of the `<body>` and nowhere else.'],
            ['The `required` attribute can be used on many form controls to enforce input before submit.', 'The `required` attribute only works on password fields.'],
            ['An `<input type="email">` helps browsers validate email-shaped input.', 'An `<input type="email">` guarantees that the mailbox actually exists.'],
            ['An `<input type="number">` can still submit non-visible formatting issues if not validated server-side.', 'Using `<input type="number">` means server-side validation is never necessary.'],
            ['The `placeholder` attribute is not a replacement for a visible form label.', 'A placeholder alone is always sufficient and accessible in place of a label.'],
            ['The `<fieldset>` and `<legend>` elements help group related form controls semantically.', 'The `<fieldset>` element is only for decorative borders and has no semantic meaning.'],
            ['The `checked` attribute is used for preselecting checkboxes or radio inputs.', 'The `checked` attribute is used to make text inputs read-only.'],
            ['The `disabled` attribute prevents a form control from being submitted.', 'A disabled form control is submitted normally with the rest of the form data.'],
            ['The `readonly` attribute allows a form control’s value to be submitted while preventing edits.', 'A readonly field is completely ignored during form submission.'],
            ['The `<datalist>` element can provide suggested input values for some text-based controls.', 'The `<datalist>` element replaces the need for an `<input>` element.'],
            ['The `<textarea>` element is used for multi-line user input.', 'The `<textarea>` element is only for displaying preformatted code blocks.'],
            ['The `autocomplete` attribute can help browsers fill known user information.', 'The `autocomplete` attribute disables browser support for previously entered values in all cases.'],
            ['The `<meta charset="UTF-8">` declaration helps browsers interpret document encoding correctly.', 'The `<meta charset>` declaration must appear after all scripts to work correctly.'],
            ['The `<title>` element is required inside the document head in standard HTML documents.', 'The `<title>` element belongs inside the `<body>` because it is visible page content.'],
            ['The `defer` attribute allows an external script to execute after HTML parsing completes.', 'The `defer` attribute makes a script execute immediately before any HTML is parsed.'],
            ['The `async` attribute can make script execution order unpredictable across multiple scripts.', 'Multiple `async` scripts always execute in the exact order they appear in the markup.'],
            ['The `<link rel="stylesheet">` element is used to load external CSS.', 'The `<link rel="stylesheet">` element is used to define JavaScript variables globally.'],
            ['The `<meta name="viewport">` tag is commonly used for responsive behavior on mobile devices.', 'The viewport meta tag is used to embed audio files in HTML.'],
            ['The `<base>` element can affect how relative URLs are resolved in a document.', 'The `<base>` element defines the default font size for a page.'],
            ['The `<noscript>` element can provide fallback content when scripting is unavailable.', 'The `<noscript>` element is used to preload images for JavaScript animations.'],
            ['The `lang` attribute on the `<html>` element helps assistive technologies interpret language.', 'The `lang` attribute only affects CSS colors and has no accessibility value.'],
            ['The `id` attribute should be unique within a document.', 'It is best practice to reuse the same `id` on many elements for easier styling.'],
            ['The `<table>` element is intended for tabular data, not general page layout.', 'The `<table>` element is the preferred modern tool for all responsive page layouts.'],
            ['The `<th>` element is used for header cells in a table.', 'The `<th>` element is used only for table captions.'],
            ['The `<caption>` element can provide a title or summary for a table.', 'The `<caption>` element can only be used outside the `<table>` element.'],
            ['The `scope` attribute on `<th>` can help screen readers understand table headers.', 'The `scope` attribute is used to set the width of a table column.'],
            ['The `<colgroup>` element can be used to define properties for one or more table columns.', 'The `<colgroup>` element is used to create collapsible rows inside a table body.'],
            ['The `<picture>` element can be used for art direction or responsive image sources.', 'The `<picture>` element permanently replaces the need for an `<img>` element.'],
            ['The `controls` attribute on `<video>` or `<audio>` displays browser playback controls.', 'The `controls` attribute automatically enables autoplay on media elements.'],
            ['The `<track>` element can be used to provide captions or subtitles for video.', 'The `<track>` element is used to define animation timelines in CSS.'],
            ['The `<source>` element can be used inside media elements to offer multiple file formats.', 'The `<source>` element is only valid inside a `<table>`.'],
            ['The `<iframe>` element embeds another browsing context within the page.', 'The `<iframe>` element is used to create semantic sidebars.'],
            ['The `loading="lazy"` attribute can defer offscreen image loading in supporting browsers.', 'The `loading="lazy"` attribute forces an image to load before all other assets.'],
            ['The `<details>` element can provide a built-in expandable disclosure widget.', 'The `<details>` element is used only for storing hidden SEO metadata.'],
            ['The `<summary>` element acts as the visible label for a `<details>` element.', 'The `<summary>` element is only valid inside a `<figure>` element.'],
            ['The `download` attribute on an `<a>` element can suggest downloading the linked resource.', 'The `download` attribute on `<a>` guarantees a file will always download on every origin.'],
            ['The `target="_blank"` attribute opens a link in a new browsing context.', 'The `target="_blank"` attribute is used to validate form fields before submission.'],
            ['Using `rel="noopener"` with `target="_blank"` can improve security.', 'Using `rel="noopener"` causes a hyperlink to stop working entirely.'],
            ['The `<nav>` element is intended for major navigation blocks.', 'The `<nav>` element should wrap every list on the page, even unrelated content lists.'],
            ['The `<time>` element can mark up dates or times in machine-readable form.', 'The `<time>` element is required for displaying a clock with JavaScript.'],
            ['The `datetime` attribute can provide a machine-readable value for `<time>`.', 'The `datetime` attribute is used to style the `<time>` element in CSS.'],
            ['The `<figure>` and `<figcaption>` elements are useful for self-contained illustrated content.', 'The `<figcaption>` element can only be used inside a `<footer>`.'],
            ['The `<mark>` element represents highlighted or relevant text.', 'The `<mark>` element is used to define a horizontal rule.'],
            ['The `<abbr>` element can mark up abbreviations and optionally include an expansion in `title`.', 'The `<abbr>` element is used to create browser toolbars.'],
            ['The `<code>` element is intended for inline code fragments.', 'The `<code>` element automatically preserves all whitespace like a `<pre>` element.'],
            ['The `<pre>` element preserves whitespace and line breaks as written in the source.', 'The `<pre>` element converts all whitespace to a single space.'],
            ['The `<strong>` element conveys importance, not just bold styling.', 'The `<strong>` element is purely decorative and never carries semantic meaning.'],
            ['The `<em>` element indicates stress emphasis.', 'The `<em>` element is only valid inside headings.'],
            ['The `hidden` attribute hides content from rendering by default.', 'The `hidden` attribute makes content searchable and always visible to users.'],
            ['The `contenteditable` attribute can make an element editable in the browser.', 'The `contenteditable` attribute can only be applied to form elements.'],
            ['The `<template>` element stores inert DOM content that is not rendered immediately.', 'The `<template>` element displays its content exactly where it is declared by default.'],
            ['The `<slot>` element is used with Web Components and shadow DOM.', 'The `<slot>` element is used to create SQL-style placeholders in HTML forms.'],
            ['The `<dialog>` element is intended for dialogs or modal interactions.', 'The `<dialog>` element is the semantic replacement for the `<form>` element.'],
            ['The `open` attribute can indicate that a `<details>` or `<dialog>` element is open.', 'The `open` attribute is used only with `<input type="file">`.'],
            ['The `multiple` attribute allows selecting more than one file in `<input type="file">` or multiple options in some controls.', 'The `multiple` attribute can only be used on `<textarea>`.'],
            ['The `pattern` attribute can apply regular-expression-based validation to certain text inputs.', 'The `pattern` attribute can be used to validate numeric ranges on `<input type="number">` only.'],
            ['The `minlength` attribute can help validate the minimum length of text input.', 'The `minlength` attribute automatically encrypts long text values before submission.'],
            ['The `aria-label` attribute can provide an accessible name when visible text is not present.', 'The `aria-label` attribute replaces the need for semantic HTML in every situation.'],
            ['The `role` attribute can supplement semantics when needed, but native elements are often preferred.', 'The `role` attribute should always override semantic HTML, even when native elements already fit.'],
            ['A `<div>` has no built-in semantic meaning by itself.', 'A `<div>` automatically tells assistive technologies that it is a navigation landmark.'],
            ['A `<span>` is an inline generic container with no semantic meaning by default.', 'A `<span>` is a required replacement for every paragraph element.'],
            ['The `<ol>` element represents an ordered list.', 'The `<ol>` element is used for image galleries only.'],
            ['The `<ul>` element represents an unordered list.', 'The `<ul>` element is invalid unless each list item contains a form control.'],
            ['The `<dl>` element is used for description lists.', 'The `<dl>` element is used only for nested navigation menus.'],
            ['The `<dt>` element represents a term or name in a description list.', 'The `<dt>` element is used to define table rows.'],
            ['The `<dd>` element represents the description, value, or details for a term.', 'The `<dd>` element can only be used as a direct child of `<table>`.'],
            ['The `for` attribute on `<label>` should match the target control’s `id` when using explicit association.', 'The `for` attribute on `<label>` should match the target control’s `name` and not its `id`.'],
            ['Nested interactive elements, such as a `<button>` inside another `<button>`, should generally be avoided.', 'Placing a `<button>` inside another `<button>` is recommended for grouped actions.'],
            ['The `srcset` attribute can help browsers choose an image resource based on device conditions.', 'The `srcset` attribute is only valid on `<script>` elements.'],
            ['The `<meta name="description">` tag is used to provide a page summary for search and previews.', 'The `<meta name="description">` tag controls the page favicon.'],
            ['The `<link rel="canonical">` tag can indicate the preferred canonical URL for a page.', 'The canonical link tag is used to preload fonts only.'],
            ['The `<progress>` element represents task completion progress.', 'The `<progress>` element is intended only for decorative bars unrelated to task state.'],
            ['The `<meter>` element is intended for scalar measurements within a known range.', 'The `<meter>` element is used to submit hidden values in forms.'],
            ['The `value` attribute on `<progress>` should not exceed the `max` value.', 'The `value` on `<progress>` should always be larger than `max` for better visibility.'],
            ['The `accept` attribute on `<input type="file">` can hint which file types are expected.', 'The `accept` attribute guarantees the user cannot bypass file type restrictions.'],
            ['The `sandbox` attribute on `<iframe>` can restrict embedded content capabilities.', 'The `sandbox` attribute on `<iframe>` is used to center the frame horizontally.'],
            ['A boolean attribute such as `required` does not need a custom value to be active.', 'A boolean attribute such as `required` only works when written as `required="true"`.' ],
            ['The `<meta http-equiv="refresh">` tag can trigger timed redirects, though it should be used carefully.', 'The `http-equiv="refresh"` meta tag is the recommended primary replacement for HTTP redirects in all cases.'],
            ['The `<wbr>` element suggests an optional line break opportunity.', 'The `<wbr>` element forces a hard page refresh when clicked.'],
            ['The `<bdi>` element helps isolate bidirectional text from surrounding text direction.', 'The `<bdi>` element is used to embed videos inline.'],
            ['The `<bdo>` element can override the current text direction.', 'The `<bdo>` element is used to define background gradients in HTML.'],
            ['The `poster` attribute on `<video>` can specify an image shown before playback starts.', 'The `poster` attribute on `<video>` is used to define subtitle language.'],
            ['The `<map>` and `<area>` elements can define clickable regions within an image map.', 'The `<area>` element is used only for CSS grid layouts.'],
            ['The `shape` and `coords` attributes are used with `<area>` for image maps.', 'The `shape` and `coords` attributes are required on every hyperlink on the page.'],
            ['The `<input type="search">` element is intended for search terms and may receive browser-specific styling.', 'The `<input type="search">` element behaves as a hidden field and is not editable.'],
            ['The `<input type="url">` element can help validate URL-shaped input.', 'The `<input type="url">` element guarantees the URL returns a successful HTTP response.'],
            ['The `novalidate` attribute on a `<form>` disables built-in browser validation on submit.', 'The `novalidate` attribute enables stricter browser validation than normal.'],
            ['A `<script type="module">` runs in module scope and supports `import` syntax.', 'A `<script type="module">` behaves exactly like a classic script and does not support `import`.'],
            ['The `<html>` element is the root element of an HTML document.', 'The `<body>` element is the root element and `<html>` is optional in valid HTML.'],
            ['The `<body>` element contains the document’s rendered content.', 'The `<body>` element is intended only for metadata and never for visible content.'],
            ['The `<head>` element contains metadata, not regular rendered page content.', 'The `<head>` element is where visible articles and forms should be placed.'],
            ['HTML comments use the `<!-- comment -->` syntax.', 'HTML comments use the `// comment` syntax inside markup.'],
        ];
    }

    private function mcqs(): array
    {
        return [
            ['Which element is most appropriate for a self-contained blog post on a homepage?', ['<section>', '<article>', '<aside>', '<div>'], '<article>'],
            ['Which element is most appropriate for the primary navigation links of a site?', ['<main>', '<nav>', '<header>', '<section>'], '<nav>'],
            ['Which element is best for the dominant unique content of a page?', ['<aside>', '<footer>', '<main>', '<mark>'], '<main>'],
            ['Which element should be used for supplementary content such as a related-links sidebar?', ['<article>', '<main>', '<aside>', '<summary>'], '<aside>'],
            ['Which element is designed to wrap thematic grouping of content, often with a heading?', ['<section>', '<span>', '<b>', '<label>'], '<section>'],
            ['Which element is correct for a page or section intro containing heading and metadata?', ['<header>', '<meter>', '<legend>', '<figure>'], '<header>'],
            ['Which element is best for author info and copyright details at the end of a section?', ['<caption>', '<footer>', '<colgroup>', '<source>'], '<footer>'],
            ['Which element is the right choice for a machine-readable date or time?', ['<mark>', '<abbr>', '<time>', '<code>'], '<time>'],
            ['Which element is best for an image with a caption?', ['<figure>', '<article>', '<nav>', '<output>'], '<figure>'],
            ['Which element provides the caption for content wrapped in `<figure>`?', ['<legend>', '<summary>', '<figcaption>', '<caption>'], '<figcaption>'],
            ['Which element is best for a generic block container with no semantic meaning?', ['<div>', '<section>', '<article>', '<main>'], '<div>'],
            ['Which element is best for a generic inline container with no semantic meaning?', ['<span>', '<section>', '<header>', '<details>'], '<span>'],
            ['Which element should be used to emphasize importance semantically?', ['<b>', '<strong>', '<i>', '<u>'], '<strong>'],
            ['Which element represents stress emphasis semantically?', ['<em>', '<small>', '<b>', '<s>'], '<em>'],
            ['Which element is intended for inline code snippets?', ['<pre>', '<code>', '<kbd>', '<var>'], '<code>'],
            ['Which element preserves whitespace and line breaks as written in source?', ['<code>', '<mark>', '<pre>', '<small>'], '<pre>'],
            ['Which element should wrap a form title or caption inside a `<fieldset>`?', ['<summary>', '<legend>', '<caption>', '<label>'], '<legend>'],
            ['Which element groups related form controls semantically?', ['<fieldset>', '<option>', '<template>', '<meter>'], '<fieldset>'],
            ['Which attribute explicitly associates a `<label>` with a form control by id?', ['name', 'for', 'aria-label', 'autocomplete'], 'for'],
            ['Which input type is most appropriate for collecting an email address?', ['text', 'email', 'search', 'tel'], 'email'],
            ['Which input type is most appropriate for a calendar date?', ['datetime', 'calendar', 'date', 'timestamp'], 'date'],
            ['Which input type is most appropriate for a phone number on mobile keyboards?', ['tel', 'number', 'phone', 'contact'], 'tel'],
            ['Which element is best for multiline user input?', ['<input type="text">', '<textarea>', '<output>', '<label>'], '<textarea>'],
            ['Which element provides predefined suggestions that can be attached to an input?', ['<select>', '<datalist>', '<optgroup>', '<fieldset>'], '<datalist>'],
            ['Which attribute makes a field mandatory before form submission in browser validation?', ['readonly', 'required', 'hidden', 'selected'], 'required'],
            ['Which attribute prevents editing while still allowing a field value to submit?', ['disabled', 'readonly', 'hidden', 'checked'], 'readonly'],
            ['Which attribute removes a field from interactive editing and from form submission?', ['required', 'disabled', 'autofocus', 'selected'], 'disabled'],
            ['Which attribute can help browsers provide autofill hints like name or email?', ['autocomplete', 'pattern', 'placeholder', 'minlength'], 'autocomplete'],
            ['Which attribute is best for showing example text inside an empty field?', ['value', 'placeholder', 'title', 'for'], 'placeholder'],
            ['Which element is used to create a drop-down selection list?', ['<datalist>', '<select>', '<menu>', '<textarea>'], '<select>'],
            ['Which element defines an option inside a `<select>`?', ['<choice>', '<option>', '<item>', '<opt>'], '<option>'],
            ['Which element groups related options inside a `<select>`?', ['<fieldset>', '<optgroup>', '<group>', '<legend>'], '<optgroup>'],
            ['Which attribute is used to accept multiple file selections in a file input?', ['multiple', 'many', 'list', 'several'], 'multiple'],
            ['Which attribute hints allowed file types in `<input type="file">`?', ['pattern', 'accept', 'capture', 'max'], 'accept'],
            ['Which element is best for displaying calculated form output?', ['<result>', '<output>', '<meter>', '<summary>'], '<output>'],
            ['Which attribute can apply regex validation to suitable text-based inputs?', ['accept', 'pattern', 'scope', 'method'], 'pattern'],
            ['Which input type is best for a quantity that may use browser increment controls?', ['range', 'email', 'number', 'search'], 'number'],
            ['Which input type is meant for a URL value?', ['uri', 'url', 'link', 'site'], 'url'],
            ['Which input type is meant for search terms and may receive search-specific browser styling?', ['find', 'filter', 'search', 'query'], 'search'],
            ['Which attribute on a form disables built-in browser validation when submitting?', ['novalidate', 'disablevalidation', 'validate="off"', 'skip'], 'novalidate'],
            ['Which element should contain the page title and metadata?', ['<body>', '<head>', '<main>', '<footer>'], '<head>'],
            ['Which element sets the title shown in the browser tab?', ['<meta>', '<caption>', '<title>', '<summary>'], '<title>'],
            ['Which tag is used to declare the document character encoding?', ['<charset>', '<meta charset="UTF-8">', '<encoding>', '<title charset="UTF-8">'], '<meta charset="UTF-8">'],
            ['Which tag is commonly used to connect an external stylesheet?', ['<style src="app.css">', '<css href="app.css">', '<link rel="stylesheet" href="app.css">', '<script href="app.css">'], '<link rel="stylesheet" href="app.css">'],
            ['Which tag is used for embedded CSS rules inside the document head?', ['<style>', '<css>', '<script>', '<design>'], '<style>'],
            ['Which attribute on `<script>` tells the browser not to block HTML parsing and to preserve order after parsing?', ['async', 'defer', 'lazy', 'moduleonly'], 'defer'],
            ['Which attribute on `<script>` may execute as soon as the file downloads, regardless of other async scripts?', ['ordered', 'async', 'delay', 'defer'], 'async'],
            ['Which value should be used on the viewport meta tag for responsive width?', ['device-scale', 'auto', 'device-width', 'screen-width'], 'device-width'],
            ['Which tag can define a canonical URL for the page?', ['<meta name="canonical">', '<link rel="canonical">', '<canonical>', '<base canonical>'], '<link rel="canonical">'],
            ['Which tag can provide a short summary for search snippets?', ['<summary>', '<meta name="description">', '<description>', '<caption>'], '<meta name="description">'],
            ['Which tag should be used to preload or connect to another resource origin?', ['<connect>', '<preload>', '<link>', '<origin>'], '<link>'],
            ['Which attribute on `<html>` indicates the page language?', ['language', 'lang', 'locale', 'xml:lang only'], 'lang'],
            ['Which element provides fallback content when JavaScript is unavailable?', ['<fallback>', '<noscript>', '<template>', '<summary>'], '<noscript>'],
            ['Which element allows a browser to resolve relative URLs from a different base?', ['<meta>', '<base>', '<root>', '<origin>'], '<base>'],
            ['Which element is intended for tabular data?', ['<grid>', '<table>', '<list>', '<frame>'], '<table>'],
            ['Which element defines a header cell in a table?', ['<td>', '<th>', '<theadcell>', '<header>'], '<th>'],
            ['Which element defines a standard data cell in a table?', ['<cell>', '<th>', '<td>', '<col>'], '<td>'],
            ['Which element groups the header rows of a table?', ['<thead>', '<header>', '<thgroup>', '<top>'], '<thead>'],
            ['Which element groups the footer rows of a table?', ['<tfoot>', '<foot>', '<caption>', '<trailer>'], '<tfoot>'],
            ['Which element groups the main body rows of a table?', ['<tbody>', '<body>', '<mainrows>', '<rows>'], '<tbody>'],
            ['Which element provides a title for a table?', ['<summary>', '<caption>', '<label>', '<legend>'], '<caption>'],
            ['Which attribute on `<th>` helps indicate whether a header applies to a row or column?', ['for', 'scope', 'headerscope', 'axisonly'], 'scope'],
            ['Which element can describe one or more columns in a table?', ['<colgroup>', '<thead>', '<tbody>', '<legend>'], '<colgroup>'],
            ['Which element can define an individual column for styling or width hints?', ['<col>', '<cell>', '<column>', '<td>'], '<col>'],
            ['Which element is required for displaying an image resource?', ['<picture>', '<source>', '<img>', '<media>'], '<img>'],
            ['Which attribute on `<img>` provides alternative text?', ['title', 'label', 'alt', 'caption'], 'alt'],
            ['Which element can wrap multiple responsive image sources around a fallback `<img>`?', ['<figure>', '<picture>', '<media>', '<sourcegroup>'], '<picture>'],
            ['Which element can provide alternate media files inside `<picture>`, `<audio>`, or `<video>`?', ['<track>', '<source>', '<file>', '<stream>'], '<source>'],
            ['Which element embeds video playback content?', ['<media>', '<movie>', '<video>', '<embed-video>'], '<video>'],
            ['Which element embeds audio playback content?', ['<sound>', '<audio>', '<voice>', '<track>'], '<audio>'],
            ['Which attribute shows built-in playback controls on video or audio?', ['play', 'controls', 'toolbar', 'ui'], 'controls'],
            ['Which element provides subtitles or captions for video?', ['<source>', '<track>', '<caption>', '<transcript>'], '<track>'],
            ['Which attribute on `<video>` specifies an image shown before playback starts?', ['preview', 'poster', 'thumbnail', 'splash'], 'poster'],
            ['Which element embeds another page or browsing context?', ['<frame>', '<iframe>', '<embedpage>', '<portal>'], '<iframe>'],
            ['Which attribute on `<iframe>` is used to apply security restrictions?', ['restrict', 'policy', 'sandbox', 'secure'], 'sandbox'],
            ['Which element creates a built-in disclosure widget?', ['<dialog>', '<details>', '<expand>', '<toggle>'], '<details>'],
            ['Which element labels the visible heading of a `<details>` disclosure?', ['<label>', '<summary>', '<caption>', '<legend>'], '<summary>'],
            ['Which element is used for a native dialog or modal container?', ['<modal>', '<dialog>', '<popup>', '<overlay>'], '<dialog>'],
            ['Which attribute can hint that offscreen images should load lazily?', ['defer', 'loading="lazy"', 'async', 'preload'], 'loading="lazy"'],
            ['Which element is intended for progress toward task completion?', ['<meter>', '<progress>', '<status>', '<range>'], '<progress>'],
            ['Which element is intended for scalar measurements within a known range?', ['<progress>', '<meter>', '<measure>', '<output>'], '<meter>'],
            ['Which element should be used for a major ordered sequence of steps?', ['<ul>', '<ol>', '<dl>', '<menu>'], '<ol>'],
            ['Which element should be used for a set of unordered bullet points?', ['<ol>', '<ul>', '<dl>', '<summary>'], '<ul>'],
            ['Which element is used for term-description pairs?', ['<dl>', '<ol>', '<article>', '<fieldset>'], '<dl>'],
            ['Which element contains the term in a description list?', ['<dd>', '<dt>', '<li>', '<th>'], '<dt>'],
            ['Which element contains the description in a description list?', ['<dd>', '<dt>', '<li>', '<caption>'], '<dd>'],
            ['Which element is used to create a hyperlink?', ['<link>', '<a>', '<href>', '<nav>'], '<a>'],
            ['Which attribute on `<a>` sets the destination URL?', ['src', 'action', 'href', 'target'], 'href'],
            ['Which attribute on `<a>` suggests downloading the linked resource?', ['save', 'download', 'fetch', 'store'], 'download'],
            ['Which attribute value commonly opens a link in a new tab or window?', ['_new', '_blank', '_tab', '_next'], '_blank'],
            ['Which `rel` value is commonly paired with `_blank` for security reasons?', ['nofollow', 'noopener', 'external', 'canonical'], 'noopener'],
            ['Which attribute gives an element a unique identifier in the document?', ['class', 'id', 'name', 'key'], 'id'],
            ['Which attribute assigns one or more CSS classes to an element?', ['role', 'id', 'class', 'style'], 'class'],
            ['Which global attribute hides an element by default?', ['hidden', 'display', 'mask', 'secret'], 'hidden'],
            ['Which global attribute makes an element editable in place?', ['editable', 'contenteditable', 'modifable', 'designmode'], 'contenteditable'],
            ['Which element stores inert markup for later cloning or scripting?', ['<template>', '<script>', '<slot>', '<noscript>'], '<template>'],
            ['Which element is used inside Web Components to mark insertion points for light DOM?', ['<insert>', '<content>', '<slot>', '<shadow>'], '<slot>'],
            ['Which attribute on a form control can move focus there automatically when the page loads?', ['focus', 'autofocus', 'selected', 'priority'], 'autofocus'],
            ['Which element is most appropriate for marking an abbreviation?', ['<abbr>', '<small>', '<mark>', '<var>'], '<abbr>'],
            ['Which element is most appropriate for highlighted or relevant text in context?', ['<strong>', '<mark>', '<u>', '<b>'], '<mark>'],
            ['Which element can isolate bidirectional inline text direction from surrounding text?', ['<bdo>', '<bdi>', '<dir>', '<rtl>'], '<bdi>'],
            ['Which element overrides text direction explicitly?', ['<rtl>', '<bdo>', '<bdi>', '<dir>'], '<bdo>'],
            ['Which element is most appropriate for keyboard input examples like Ctrl+C?', ['<kbd>', '<code>', '<var>', '<samp>'], '<kbd>'],
            ['Which element is most appropriate for sample output from a program?', ['<output>', '<samp>', '<code>', '<var>'], '<samp>'],
            ['Which element is most appropriate for a variable name in technical prose?', ['<var>', '<kbd>', '<mark>', '<sup>'], '<var>'],
            ['Which element is commonly used to insert a thematic break between content sections?', ['<break>', '<hr>', '<br>', '<separator>'], '<hr>'],
            ['Which element inserts a line break within text without starting a new paragraph?', ['<lb>', '<br>', '<break>', '<hr>'], '<br>'],
            ['Which element is correct for superscript content such as exponents?', ['<sub>', '<sup>', '<small>', '<strong>'], '<sup>'],
            ['Which element is correct for subscript content such as chemical formulas?', ['<sup>', '<sub>', '<small>', '<mark>'], '<sub>'],
            ['Which element is used to define clickable regions inside an image map?', ['<map>', '<area>', '<region>', '<hotspot>'], '<area>'],
            ['Which element wraps the definition of an image map?', ['<imagemap>', '<map>', '<picture>', '<coords>'], '<map>'],
            ['Which attribute on `<area>` defines the linked destination?', ['src', 'href', 'target', 'action'], 'href'],
            ['Which attribute on `<area>` helps make image maps accessible?', ['alt', 'title', 'src', 'shape'], 'alt'],
            ['Which attribute on `<img>` helps the browser infer rendered size before the image loads?', ['ratio', 'width and height', 'shape', 'loading'], 'width and height'],
            ['Which element should be used for a contact information block for the nearest article or page owner?', ['<address>', '<footer>', '<aside>', '<cite>'], '<address>'],
            ['Which element is best for citing the title of a referenced creative work?', ['<cite>', '<q>', '<blockquote>', '<mark>'], '<cite>'],
            ['Which element is used for an inline quotation?', ['<quote>', '<q>', '<blockquote>', '<cite>'], '<q>'],
            ['Which element is used for a longer block quotation?', ['<blockquote>', '<q>', '<cite>', '<pre>'], '<blockquote>'],
            ['Which attribute can provide an accessible name when no visible label exists?', ['aria-label', 'for', 'alt', 'scope'], 'aria-label'],
            ['Which attribute is useful for referencing another element that labels the current element?', ['aria-labelledby', 'headers', 'label-for', 'for'], 'aria-labelledby'],
            ['Which attribute indicates the expanded or collapsed state of a disclosure control?', ['aria-open', 'aria-expanded', 'aria-hidden', 'open-state'], 'aria-expanded'],
            ['Which attribute indicates whether a popup, dialog, or submenu is associated with a control?', ['aria-haspopup', 'aria-dialog', 'aria-popup', 'popup'], 'aria-haspopup'],
            ['Which attribute is commonly used to tie helper or error text to a form field for assistive tech?', ['aria-details', 'aria-describedby', 'for', 'scope'], 'aria-describedby'],
            ['Which attribute indicates that an element and its subtree should be hidden from assistive technologies?', ['aria-hidden', 'hidden', 'inert', 'readonly'], 'aria-hidden'],
            ['Which HTML element is usually preferable to adding `role="button"` on a non-button element?', ['<button>', '<span>', '<div>', '<mark>'], '<button>'],
            ['Which HTML element is usually preferable to adding `role="link"` on a generic container?', ['<section>', '<a>', '<div>', '<span>'], '<a>'],
            ['Which script type enables ES module imports in the browser?', ['classic', 'module', 'esm/js', 'import'], 'module'],
            ['Which attribute on `<script>` is required to point to an external script file?', ['href', 'src', 'file', 'path'], 'src'],
            ['Which element is the root element of a standard HTML document?', ['<body>', '<html>', '<head>', '<main>'], '<html>'],
            ['Which declaration should appear at the top of an HTML5 document?', ['<!HTML5>', '<!DOCTYPE html>', '<doctype html5>', '<html version="5">'], '<!DOCTYPE html>'],
            ['Which element contains the visible rendered page content?', ['<head>', '<body>', '<title>', '<meta>'], '<body>'],
            ['Which element is correct for responsive sources around an image while still including a fallback image?', ['<source>', '<picture>', '<figure>', '<embed>'], '<picture>'],
        ];
    }
}
