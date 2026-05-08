<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BackendBasicsQuestionSeeder extends Seeder
{
   

    public function run(): void
    {
        $now = now();

        DB::table('master_documents')->updateOrInsert(
            ['doc_number' => 'BACKEND-BASICS-001'],
            [
                'doc_name' => 'MySQL + PHP Basics Question Bank',
                'version' => '1.0',
                'doc_type' => 'Others',
                'file_path' => 'master_docs/backend-basics-question-bank.json',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $documentId = DB::table('master_documents')
            ->where('doc_number', 'BACKEND-BASICS-001')
            ->value('id');

        DB::table('master_questions')
            ->where('master_document_id', $documentId)
            ->delete();

        $questionRows = array_map(function ($q) use ($documentId, $now) {
            return [
                'master_document_id' => $documentId,
                'question_text' => $q['question_text'],
                'question_type' => $q['question_type'],
                'options' => $q['options'] ? json_encode($q['options']) : null,
                'correct_answer' => $q['correct_answer'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $this->buildQuestions());

        foreach (array_chunk($questionRows, 100) as $chunk) {
            DB::table('master_questions')->insert($chunk);
        }
    }

    private function buildQuestions(): array
    {
        $questions = [];

        foreach ($this->yesNoPairs() as [$true, $false]) {
            $questions[] = [
                'question_text' => $true,
                'question_type' => 'yes_no',
                'correct_answer' => 'Yes',
                'options' => null,
            ];

            $questions[] = [
                'question_text' => $false,
                'question_type' => 'yes_no',
                'correct_answer' => 'No',
                'options' => null,
            ];
        }

        foreach ($this->mcqs() as [$q, $opts, $ans]) {
            $questions[] = [
                'question_text' => $q,
                'question_type' => 'mcq',
                'correct_answer' => $ans,
                'options' => $opts,
            ];
        }

        // if (count($questions) !== 200) {
        //     throw new \RuntimeException('Expected exactly 200 questions, got ' . count($questions));
        // }

        return $questions;
    }

    private function yesNoPairs(): array
    {
        return [
            // ===== MYSQL (20 pairs = 40 questions) =====
            ['MySQL uses SQL language.', 'MySQL uses only JavaScript.'],
            ['Primary key must be unique.', 'Primary key can be duplicate.'],
            ['Foreign key links tables.', 'Foreign key deletes tables.'],
            ['SELECT retrieves data.', 'SELECT deletes data.'],
            ['INSERT adds data.', 'INSERT removes data.'],
            ['UPDATE modifies data.', 'UPDATE creates tables.'],
            ['DELETE removes rows.', 'DELETE modifies structure.'],
            ['WHERE filters records.', 'WHERE sorts records.'],
            ['ORDER BY sorts data.', 'ORDER BY deletes data.'],
            ['JOIN combines tables.', 'JOIN splits tables.'],
            ['INNER JOIN returns matching rows.', 'INNER JOIN returns all rows always.'],
            ['LEFT JOIN includes left table.', 'LEFT JOIN excludes left table.'],
            ['Database stores structured data.', 'Database stores only images.'],
            ['Table contains rows.', 'Table contains only columns.'],
            ['Column defines data type.', 'Column stores queries.'],
            ['Index improves performance.', 'Index slows queries always.'],
            ['AUTO_INCREMENT generates ids.', 'AUTO_INCREMENT deletes rows.'],
            ['NULL means no value.', 'NULL means zero always.'],
            ['GROUP BY groups data.', 'GROUP BY deletes data.'],
            ['HAVING filters groups.', 'HAVING creates tables.'],

            // ===== PHP (20 pairs = 40 questions) =====
            ['PHP is server-side.', 'PHP is only client-side.'],
            ['PHP runs on server.', 'PHP runs only in browser.'],
            ['Variables start with $.', 'Variables start with %.'],
            ['echo outputs text.', 'echo deletes variables.'],
            ['PHP supports arrays.', 'PHP does not support arrays.'],
            ['Functions reuse code.', 'Functions delete code.'],
            ['include loads files.', 'include removes files.'],
            ['require is mandatory include.', 'require is optional always.'],
            ['isset checks variable.', 'isset deletes variable.'],
            ['empty checks value.', 'empty assigns value.'],
            ['POST sends data securely.', 'POST exposes data in URL.'],
            ['GET sends data via URL.', 'GET hides data.'],
            ['Sessions store user data.', 'Sessions delete user data.'],
            ['Cookies stored in browser.', 'Cookies stored only in DB.'],
            ['PHP supports OOP.', 'PHP does not support OOP.'],
            ['Class defines structure.', 'Class stores SQL only.'],
            ['Object is instance of class.', 'Object is a function.'],
            ['Constructor initializes object.', 'Constructor deletes object.'],
            ['Inheritance reuses code.', 'Inheritance removes code.'],
            ['PHP connects MySQL.', 'PHP cannot use MySQL.'],

            // ===== MVC (10 pairs = 20 questions) =====
            // ['MVC separates concerns.', 'MVC mixes everything.'],
            // ['Model handles data.', 'Model handles UI.'],
            // ['View shows UI.', 'View handles DB.'],
            // ['Controller manages logic.', 'Controller stores HTML only.'],
            // ['MVC improves maintainability.', 'MVC makes code messy.'],
            // ['Controller connects Model and View.', 'Controller disconnects layers.'],
            // ['Model interacts DB.', 'Model renders HTML.'],
            // ['View should not contain logic.', 'View contains all logic.'],
            // ['MVC used in frameworks.', 'MVC never used in frameworks.'],
            // ['Laravel uses MVC.', 'Laravel does not use MVC.'],
        ];
    }

    private function mcqs(): array
    {
        return [
            // ===== MYSQL (34) =====
            ['Which command fetches data?', ['SELECT','INSERT','DELETE','DROP'], 'SELECT'],
            ['Which command inserts data?', ['INSERT','SELECT','UPDATE','DROP'], 'INSERT'],
            ['Which removes data?', ['DELETE','SELECT','INSERT','JOIN'], 'DELETE'],
            ['Which updates data?', ['UPDATE','INSERT','DELETE','DROP'], 'UPDATE'],
            ['Primary key is?', ['Unique','Duplicate','Optional','Null'], 'Unique'],
            ['Foreign key is?', ['Relation','Delete','Index','View'], 'Relation'],
            ['Which joins tables?', ['JOIN','WHERE','GROUP','ORDER'], 'JOIN'],
            ['Which sorts data?', ['ORDER BY','GROUP BY','WHERE','JOIN'], 'ORDER BY'],
            ['Which groups data?', ['GROUP BY','ORDER BY','JOIN','WHERE'], 'GROUP BY'],
            ['Which filters groups?', ['HAVING','WHERE','ORDER','JOIN'], 'HAVING'],
            ['Which keyword filters rows?', ['WHERE','HAVING','JOIN','ORDER'], 'WHERE'],
            ['Which DB type is MySQL?', ['Relational','NoSQL','Graph','Document'], 'Relational'],
            ['Which command creates table?', ['CREATE','INSERT','DELETE','UPDATE'], 'CREATE'],
            ['Which deletes table?', ['DROP','DELETE','REMOVE','CLEAR'], 'DROP'],
            ['Which modifies table?', ['ALTER','UPDATE','DELETE','INSERT'], 'ALTER'],
            ['Which adds column?', ['ALTER','CREATE','INSERT','JOIN'], 'ALTER'],
            ['Which removes column?', ['ALTER','DELETE','DROP','JOIN'], 'ALTER'],
            ['Which constraint ensures uniqueness?', ['UNIQUE','NULL','DEFAULT','INDEX'], 'UNIQUE'],
            ['Which allows null?', ['NULL','NOT NULL','PRIMARY','INDEX'], 'NULL'],
            ['Which prevents null?', ['NOT NULL','NULL','DEFAULT','PRIMARY'], 'NOT NULL'],
            ['Which default value?', ['DEFAULT','NULL','AUTO','INDEX'], 'DEFAULT'],
            ['Which auto id?', ['AUTO_INCREMENT','DEFAULT','INDEX','PRIMARY'], 'AUTO_INCREMENT'],
            ['Which index speeds query?', ['INDEX','JOIN','GROUP','WHERE'], 'INDEX'],
            ['Which combines rows?', ['UNION','JOIN','WHERE','GROUP'], 'UNION'],
            ['Which shows structure?', ['DESCRIBE','SHOW','SELECT','VIEW'], 'DESCRIBE'],
            ['Which shows DBs?', ['SHOW DATABASES','SELECT','JOIN','VIEW'], 'SHOW DATABASES'],
            ['Which selects DB?', ['USE','SELECT','JOIN','OPEN'], 'USE'],
            ['Which deletes DB?', ['DROP','DELETE','REMOVE','CLEAR'], 'DROP'],
            ['Which counts rows?', ['COUNT','SUM','AVG','MAX'], 'COUNT'],
            ['Which finds max?', ['MAX','MIN','AVG','COUNT'], 'MAX'],
            ['Which finds min?', ['MIN','MAX','SUM','COUNT'], 'MIN'],
            ['Which averages?', ['AVG','SUM','COUNT','MAX'], 'AVG'],
            ['Which sums?', ['SUM','AVG','COUNT','MAX'], 'SUM'],
            ['Which pattern match?', ['LIKE','MATCH','SEARCH','FIND'], 'LIKE'],

            // ===== PHP (33) =====
            ['PHP stands for?', ['Hypertext Preprocessor','Personal Home Page','Both','None'], 'Both'],
            ['PHP file extension?', ['.php','.html','.js','.css'], '.php'],
            ['Variable symbol?', ['$','#','@','%'], '$'],
            ['Output function?', ['echo','print_r','var_dump','all'], 'echo'],
            ['Array function?', ['array()','list()','set()','map()'], 'array()'],
            ['Include file?', ['include','add','require','import'], 'include'],
            ['Must include?', ['require','include','add','import'], 'require'],
            ['Check variable?', ['isset','check','exist','defined'], 'isset'],
            ['Empty function?', ['empty','null','isset','check'], 'empty'],
            ['Send via URL?', ['GET','POST','PUT','PATCH'], 'GET'],
            ['Secure send?', ['POST','GET','PUT','PATCH'], 'POST'],
            ['Session start?', ['session_start','start_session','init','begin'], 'session_start'],
            ['Destroy session?', ['session_destroy','end','destroy','stop'], 'session_destroy'],
            ['Cookie set?', ['setcookie','cookie','addcookie','makecookie'], 'setcookie'],
            ['OOP keyword?', ['class','object','function','var'], 'class'],
            ['Create object?', ['new','create','init','make'], 'new'],
            ['Constructor name?', ['__construct','init','create','start'], '__construct'],
            ['Inheritance keyword?', ['extends','inherits','include','use'], 'extends'],
            ['Access public?', ['public','private','protected','static'], 'public'],
            ['Access private?', ['private','public','protected','static'], 'private'],
            ['Access protected?', ['protected','public','private','static'], 'protected'],
            ['Static keyword?', ['static','const','define','var'], 'static'],
            ['Constant define?', ['define','const','set','var'], 'define'],
            ['Loop for?', ['for','if','echo','switch'], 'for'],
            ['Loop while?', ['while','for','switch','if'], 'while'],
            ['Conditional?', ['if','loop','echo','print'], 'if'],
            ['Switch case?', ['switch','case','if','for'], 'switch'],
            ['Function define?', ['function','def','fun','method'], 'function'],
            ['Return value?', ['return','echo','print','give'], 'return'],
            ['String concat?', ['.','+','&','*'], '.'],
            ['Length function?', ['strlen','count','size','length'], 'strlen'],
            ['Array count?', ['count','strlen','size','length'], 'count'],
            ['Database connect?', ['mysqli','mysql','db','connect'], 'mysqli'],

            // ===== MVC (33) =====
            // ['MVC full form?', ['Model View Controller','Multiple View Code','Main View Controller','None'], 'Model View Controller'],
            // ['Model handles?', ['Data','UI','Routing','Design'], 'Data'],
            // ['View handles?', ['UI','DB','Logic','API'], 'UI'],
            // ['Controller handles?', ['Logic','UI','DB','HTML'], 'Logic'],
            // ['MVC benefit?', ['Separation','Mixing','Slowing','Blocking'], 'Separation'],
            // ['Laravel uses?', ['MVC','MVP','MVVM','None'], 'MVC'],
            // ['Model connects?', ['Database','UI','Controller','Route'], 'Database'],
            // ['View contains?', ['HTML','SQL','Logic','Routes'], 'HTML'],
            // ['Controller connects?', ['Model & View','View only','DB only','None'], 'Model & View'],
            // ['MVC improves?', ['Maintainability','Complexity','Errors','Latency'], 'Maintainability'],
            // ['Routing handled by?', ['Controller','Model','View','DB'], 'Controller'],
            // ['Business logic in?', ['Model','View','Controller','Route'], 'Model'],
            // ['Presentation logic in?', ['View','Model','Controller','DB'], 'View'],
            // ['User request handled by?', ['Controller','Model','View','DB'], 'Controller'],
            // ['Data returned by?', ['Model','View','Controller','Route'], 'Model'],
            // ['Blade is?', ['View','Controller','Model','Route'], 'View'],
            // ['Eloquent is?', ['Model','View','Controller','Route'], 'Model'],
            // ['Route maps to?', ['Controller','Model','View','DB'], 'Controller'],
            // ['MVC reduces?', ['Coupling','Code','Files','Speed'], 'Coupling'],
            // ['MVC increases?', ['Scalability','Errors','Duplication','Bugs'], 'Scalability'],
            // ['Which layer DB?', ['Model','View','Controller','Route'], 'Model'],
            // ['Which layer UI?', ['View','Model','Controller','Route'], 'View'],
            // ['Which layer logic?', ['Controller','Model','View','Route'], 'Controller'],
            // ['MVC used in?', ['Frameworks','CSS','HTML','None'], 'Frameworks'],
            // ['Separation of concerns?', ['MVC','HTML','CSS','JS'], 'MVC'],
            // ['Model returns?', ['Data','HTML','Routes','CSS'], 'Data'],
            // ['View returns?', ['HTML','Data','Logic','Routes'], 'HTML'],
            // ['Controller returns?', ['Response','Data','HTML','DB'], 'Response'],
            // ['MVC helps?', ['Testing','Breaking','Deleting','Slowing'], 'Testing'],
            // ['Framework example?', ['Laravel','Bootstrap','React','Vue'], 'Laravel'],
            // ['MVC architecture?', ['Design Pattern','Language','DB','API'], 'Design Pattern'],
            // ['Which improves code?', ['MVC','HTML','CSS','JS'], 'MVC'],
            // ['Which separates layers?', ['MVC','API','DB','HTML'], 'MVC'],
        ];
    }
}
