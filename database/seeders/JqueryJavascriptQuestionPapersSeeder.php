<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JqueryJavascriptQuestionPapersSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPaper([
            'doc_name' => 'Entry Level jQuery and JavaScript Question Paper',
            'doc_number' => 'JS-JQ-ENTRY-001',
            'version' => '1.0',
            'doc_type' => 'Others',
            'file_path' => 'master_docs/jquery-js-entry-question-paper.html',
        ], $this->entryLevelQuestions());

        $this->seedPaper([
            'doc_name' => 'Mid Core jQuery and JavaScript Question Paper',
            'doc_number' => 'JS-JQ-MID-001',
            'version' => '1.0',
            'doc_type' => 'Others',
            'file_path' => 'master_docs/jquery-js-mid-core-question-paper.html',
        ], $this->midCoreQuestions());
    }

    private function seedPaper(array $document, array $questions): void
    {
        $now = now();

        DB::table('master_documents')->updateOrInsert(
            ['doc_number' => $document['doc_number']],
            array_merge($document, [
                'created_at' => $now,
                'updated_at' => $now,
            ])
        );

        $documentId = DB::table('master_documents')
            ->where('doc_number', $document['doc_number'])
            ->value('id');

        DB::table('master_questions')
            ->where('master_document_id', $documentId)
            ->delete();

        $rows = array_map(function (array $question) use ($documentId, $now) {
            return [
                'master_document_id' => $documentId,
                'question_text' => $question['question_text'],
                'question_type' => $question['question_type'],
                'options' => $question['options'] ? json_encode($question['options']) : null,
                'correct_answer' => $question['correct_answer'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $questions);

        DB::table('master_questions')->insert($rows);
    }

    private function yesNo(string $questionText, string $correctAnswer): array
    {
        return [
            'question_text' => $questionText,
            'question_type' => 'yes_no',
            'options' => null,
            'correct_answer' => $correctAnswer,
        ];
    }

    private function mcq(string $questionText, array $options, string $correctAnswer): array
    {
        return [
            'question_text' => $questionText,
            'question_type' => 'mcq',
            'options' => $options,
            'correct_answer' => $correctAnswer,
        ];
    }

    private function entryLevelQuestions(): array
    {
        return [
            $this->yesNo('JavaScript can be used to change HTML content dynamically in the browser.', 'Yes'),
            $this->yesNo('jQuery is a server-side database language.', 'No'),
            $this->yesNo('The `let` keyword can be used to declare a block-scoped variable in JavaScript.', 'Yes'),
            $this->yesNo('The `const` keyword means the assigned variable name cannot be reassigned.', 'Yes'),
            $this->yesNo('A JavaScript array can store multiple values in a single variable.', 'Yes'),
            $this->yesNo('The `===` operator checks value equality without considering type.', 'No'),
            $this->yesNo('The `document.getElementById()` method selects an element by its id.', 'Yes'),
            $this->yesNo('A JavaScript function must always return a value explicitly.', 'No'),
            $this->yesNo('The jQuery `$()` function is commonly used to select elements.', 'Yes'),
            $this->yesNo('The jQuery `.hide()` method can hide selected elements.', 'Yes'),
            $this->yesNo('The jQuery `.val()` method is commonly used to get or set form field values.', 'Yes'),
            $this->yesNo('JavaScript object properties are always accessed only with dot notation.', 'No'),
            $this->yesNo('The `click` event can be handled using JavaScript or jQuery.', 'Yes'),
            $this->yesNo('The `parseInt()` function can convert a string into an integer.', 'Yes'),
            $this->yesNo('The `alert()` function displays a browser alert dialog.', 'Yes'),
            $this->yesNo('The `console.log()` method is commonly used for debugging output.', 'Yes'),
            $this->yesNo('The jQuery `.addClass()` method removes a CSS class from an element.', 'No'),
            $this->yesNo('The jQuery `.removeClass()` method removes a CSS class from selected elements.', 'Yes'),
            $this->yesNo('The JavaScript `for` loop can repeat code multiple times.', 'Yes'),
            $this->yesNo('The `if` statement is used for conditional execution.', 'Yes'),
            $this->yesNo('The JavaScript `NaN` value means Not-a-Number.', 'Yes'),
            $this->yesNo('The `typeof` operator can check the type of a JavaScript value.', 'Yes'),
            $this->yesNo('The jQuery `.text()` method always returns raw HTML markup.', 'No'),
            $this->yesNo('The jQuery `.html()` method can get or set HTML content.', 'Yes'),
            $this->yesNo('JavaScript strings can be enclosed in single quotes, double quotes, or template literals.', 'Yes'),
            $this->mcq('Which keyword declares a block-scoped variable that can be reassigned?', ['var', 'let', 'const', 'static'], 'let'),
            $this->mcq('Which keyword declares a variable that cannot be reassigned?', ['let', 'var', 'const', 'new'], 'const'),
            $this->mcq('Which method selects an element by id in plain JavaScript?', ['document.queryAll()', 'document.getElementById()', 'window.selectId()', 'element.byId()'], 'document.getElementById()'),
            $this->mcq('Which jQuery selector selects an element with id `menu`?', ['$("menu")', '$(".menu")', '$("#menu")', '$("*menu")'], '$("#menu")'),
            $this->mcq('Which jQuery selector selects all elements with class `active`?', ['$("*active")', '$(".active")', '$("#active")', '$("active")'], '$(".active")'),
            $this->mcq('Which JavaScript method adds an item to the end of an array?', ['push()', 'pop()', 'shift()', 'slice()'], 'push()'),
            $this->mcq('Which JavaScript method removes the last item from an array?', ['push()', 'pop()', 'unshift()', 'map()'], 'pop()'),
            $this->mcq('Which event is triggered when a user clicks an element?', ['submit', 'change', 'click', 'load'], 'click'),
            $this->mcq('Which jQuery method attaches a click handler?', ['.on("click", handler)', '.clickOnly(handler)', '.listen("click")', '.eventClick(handler)'], '.on("click", handler)'),
            $this->mcq('Which method can convert JSON text into a JavaScript object?', ['JSON.parse()', 'JSON.stringify()', 'JSON.convert()', 'parse.JSON()'], 'JSON.parse()'),
            $this->mcq('Which method converts a JavaScript object into a JSON string?', ['JSON.parse()', 'JSON.stringify()', 'Object.json()', 'JSON.text()'], 'JSON.stringify()'),
            $this->mcq('Which operator is used for strict equality?', ['==', '===', '=', '!='], '==='),
            $this->mcq('Which operator is used for logical AND?', ['&&', '||', '!', '&|'], '&&'),
            $this->mcq('Which jQuery method gets or sets CSS properties?', ['.style()', '.css()', '.class()', '.design()'], '.css()'),
            $this->mcq('Which jQuery method gets or sets an attribute?', ['.propOnly()', '.attribute()', '.attr()', '.meta()'], '.attr()'),
            $this->mcq('Which method prevents a form from submitting normally inside an event handler?', ['event.stop()', 'event.preventDefault()', 'return.submit(false)', 'form.cancel()'], 'event.preventDefault()'),
            $this->mcq('Which JavaScript structure is best for key-value pairs?', ['Array', 'Object', 'String', 'Boolean'], 'Object'),
            $this->mcq('Which value represents true or false?', ['String', 'Number', 'Boolean', 'Array'], 'Boolean'),
            $this->mcq('Which function schedules code to run after a delay?', ['setTimeout()', 'setIntervalOnce()', 'delayNow()', 'waitFor()'], 'setTimeout()'),
            $this->mcq('Which function schedules code to run repeatedly?', ['setTimeout()', 'setInterval()', 'repeatTimeout()', 'loopTimer()'], 'setInterval()'),
            $this->mcq('Which jQuery method fades an element out?', ['.fadeOut()', '.hideFade()', '.invisible()', '.fadeRemove()'], '.fadeOut()'),
            $this->mcq('Which jQuery method shows hidden selected elements?', ['.display()', '.show()', '.visible()', '.open()'], '.show()'),
            $this->mcq('Which plain JavaScript method selects the first matching CSS selector?', ['querySelector()', 'querySelectorAll()', 'getFirst()', 'selectOne()'], 'querySelector()'),
            $this->mcq('Which plain JavaScript method selects all matching CSS selectors?', ['querySelector()', 'querySelectorAll()', 'getAllByCss()', 'selectMany()'], 'querySelectorAll()'),
            $this->mcq('Which statement creates a function named `sum`?', ['function sum() {}', 'create sum() {}', 'def sum() {}', 'func sum() {}'], 'function sum() {}'),
        ];
    }

    private function midCoreQuestions(): array
    {
        return array_slice([
            $this->yesNo('A closure allows an inner function to access variables from an outer function after the outer function has returned.', 'Yes'),
            $this->yesNo('JavaScript promises are used only for styling DOM elements.', 'No'),
            $this->yesNo('Event delegation can reduce the number of event listeners needed for dynamic lists.', 'Yes'),
            $this->yesNo('The `this` value in JavaScript always refers to the global object.', 'No'),
            $this->yesNo('The `map()` method returns a new array.', 'Yes'),
            $this->yesNo('The `forEach()` method returns a transformed array.', 'No'),
            $this->yesNo('The `filter()` method returns a new array containing matching items.', 'Yes'),
            $this->yesNo('The `reduce()` method can accumulate array values into a single result.', 'Yes'),
            $this->yesNo('jQuery event namespaces can help remove specific handlers.', 'Yes'),
            $this->yesNo('The jQuery `.data()` method can read HTML `data-*` attributes.', 'Yes'),
            $this->yesNo('AJAX requests always block the browser UI until they finish.', 'No'),
            $this->yesNo('The `fetch()` API returns a Promise.', 'Yes'),
            $this->yesNo('The `async` keyword makes a function return a Promise.', 'Yes'),
            $this->yesNo('The `await` keyword can be used at any place in any non-async function.', 'No'),
            $this->yesNo('Debouncing can reduce how often a handler runs during rapid repeated events.', 'Yes'),
            $this->yesNo('Throttling ensures a function runs at most once in a defined interval.', 'Yes'),
            $this->yesNo('The DOM should be repeatedly queried inside tight loops when the same result can be cached.', 'No'),
            $this->yesNo('The jQuery `.closest()` method searches ancestors of the selected element.', 'Yes'),
            $this->yesNo('The jQuery `.find()` method searches descendants of selected elements.', 'Yes'),
            $this->yesNo('Arrow functions have their own independent `this` binding.', 'No'),
            $this->yesNo('The spread operator can copy iterable values into a new array.', 'Yes'),
            $this->yesNo('Destructuring can extract values from arrays or objects.', 'Yes'),
            $this->yesNo('The `localStorage` API stores data that persists after page refresh.', 'Yes'),
            $this->yesNo('The `sessionStorage` API is shared permanently across browser restarts.', 'No'),
            $this->yesNo('Using delegated events is useful when elements are added after page load.', 'Yes'),
            $this->mcq('Which concept lets an inner function remember variables from its outer scope?', ['Hoisting', 'Closure', 'Prototype', 'Mutation'], 'Closure'),
            $this->mcq('Which array method returns a new array of transformed values?', ['forEach()', 'map()', 'reduce()', 'some()'], 'map()'),
            $this->mcq('Which array method returns only elements that pass a condition?', ['map()', 'filter()', 'reduce()', 'join()'], 'filter()'),
            $this->mcq('Which array method is best for accumulating a total from array items?', ['reduce()', 'filter()', 'every()', 'slice()'], 'reduce()'),
            $this->mcq('Which keyword pauses execution inside an async function until a Promise settles?', ['pause', 'yield', 'await', 'hold'], 'await'),
            $this->mcq('Which API is commonly used for modern HTTP requests and returns a Promise?', ['fetch()', 'setTimeout()', 'querySelector()', 'JSON.parse()'], 'fetch()'),
            $this->mcq('Which jQuery pattern handles clicks on future `.item` elements through a parent?', ['$parent.on("click", ".item", handler)', '$(".item").futureClick(handler)', '$parent.delegateOnly(handler)', '$(".item").liveNow(handler)'], '$parent.on("click", ".item", handler)'),
            $this->mcq('Which jQuery method searches upward through ancestors?', ['.find()', '.children()', '.closest()', '.next()'], '.closest()'),
            $this->mcq('Which jQuery method searches descendants?', ['.closest()', '.find()', '.parent()', '.prev()'], '.find()'),
            $this->mcq('Which method removes a specific namespaced jQuery event handler?', ['.off("click.menu")', '.remove("click.menu")', '.clearEvent("menu")', '.unbindAll("menu")'], '.off("click.menu")'),
            $this->mcq('Which technique delays a function until rapid repeated events settle?', ['Throttling', 'Debouncing', 'Polling', 'Bubbling'], 'Debouncing'),
            $this->mcq('Which technique limits a function to run at most once per interval?', ['Debouncing', 'Throttling', 'Hoisting', 'Memoizing'], 'Throttling'),
            $this->mcq('Which storage persists beyond a single browser tab session until cleared?', ['sessionStorage', 'localStorage', 'temporaryStorage', 'memoryStorage'], 'localStorage'),
            $this->mcq('Which operator expands iterable values into another array or function call?', ['rest', 'spread', 'async', 'pipe'], 'spread'),
            $this->mcq('Which syntax extracts `name` from an object into a variable?', ['const { name } = user;', 'const [ name ] = user;', 'const name <= user;', 'const name from user;'], 'const { name } = user;'),
            $this->mcq('Which method checks whether at least one array item passes a test?', ['every()', 'some()', 'filter()', 'map()'], 'some()'),
            $this->mcq('Which method checks whether all array items pass a test?', ['some()', 'every()', 'find()', 'includes()'], 'every()'),
            $this->mcq('Which method returns the first matching array item?', ['filter()', 'find()', 'map()', 'reduce()'], 'find()'),
            $this->mcq('Which method creates a shallow copy of part of an array?', ['splice()', 'slice()', 'split()', 'shift()'], 'slice()'),
            $this->mcq('Which method changes an array by adding or removing items at an index?', ['slice()', 'splice()', 'map()', 'concat()'], 'splice()'),
            $this->mcq('Which JavaScript feature is commonly used to handle errors in synchronous code?', ['try...catch', 'if...loop', 'error...end', 'catch...throw only'], 'try...catch'),
            $this->mcq('Which method is used to attach a native event listener?', ['addEventListener()', 'listenEvent()', 'attach()', 'onNative()'], 'addEventListener()'),
            $this->mcq('Which property identifies the original element that triggered an event?', ['event.target', 'event.current', 'event.sourceOnly', 'event.originNode'], 'event.target'),
            $this->mcq('Which property identifies the element whose listener is currently running?', ['event.target', 'event.currentTarget', 'event.parentTarget', 'event.owner'], 'event.currentTarget'),
            $this->mcq('Which phase describes an event moving upward through ancestors?', ['Capturing', 'Bubbling', 'Resolving', 'Ticking'], 'Bubbling'),
            $this->mcq('Which queue processes Promise callbacks after the current synchronous code completes?', ['Macrotask queue', 'Microtask queue', 'Render queue', 'Style queue'], 'Microtask queue'),
            $this->mcq('Which function can schedule work before the next repaint?', ['requestAnimationFrame()', 'setInterval()', 'Promise.all()', 'renderNow()'], 'requestAnimationFrame()'),
        ], 0, 50);
    }
}
