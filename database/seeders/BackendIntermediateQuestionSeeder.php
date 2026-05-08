<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class BackendIntermediateQuestionSeeder extends Seeder
{

    public function run(): void
    {
        $now = now();

        DB::table('master_documents')->updateOrInsert(
            ['doc_number' => 'BACKEND-INT-001'],
            [
                'doc_name' => 'MySQL + PHP Intermediate Question Bank',
                'version' => '1.0',
                'doc_type' => 'Others',
                'file_path' => 'master_docs/backend-intermediate-question-bank.json',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $documentId = DB::table('master_documents')
            ->where('doc_number', 'BACKEND-INT-001')
            ->value('id');

        DB::table('master_questions')
            ->where('master_document_id', $documentId)
            ->delete();

        $rows = array_map(function ($q) use ($documentId, $now) {
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

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('master_questions')->insert($chunk);
        }
    }

    private function buildQuestions(): array
    {
        $questions = [];

        foreach ($this->yesNoPairs() as [$t, $f]) {
            $questions[] = [
                'question_text' => $t,
                'question_type' => 'yes_no',
                'correct_answer' => 'Yes',
                'options' => null,
            ];

            $questions[] = [
                'question_text' => $f,
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
        //     throw new \RuntimeException('Expected exactly 200 questions');
        // }

        return $questions;
    }

    private function yesNoPairs(): array
    {
        return [
            // ===== MYSQL INTERMEDIATE (25 pairs) =====
            ['INNER JOIN returns matching rows from both tables.', 'INNER JOIN returns all rows regardless of match.'],
            ['LEFT JOIN returns all rows from left table.', 'LEFT JOIN excludes unmatched left rows.'],
            ['RIGHT JOIN returns all rows from right table.', 'RIGHT JOIN ignores right table data.'],
            ['Indexes improve SELECT performance.', 'Indexes slow down SELECT queries always.'],
            ['Normalization reduces redundancy.', 'Normalization increases duplication.'],
            ['Transactions ensure atomicity.', 'Transactions allow partial updates.'],
            ['COMMIT saves transaction.', 'COMMIT rolls back changes.'],
            ['ROLLBACK undoes changes.', 'ROLLBACK commits changes.'],
            ['Foreign keys enforce referential integrity.', 'Foreign keys allow invalid references.'],
            ['GROUP BY groups rows.', 'GROUP BY deletes rows.'],
            ['HAVING filters grouped data.', 'HAVING replaces WHERE always.'],
            ['Subqueries can be nested.', 'Subqueries are not allowed.'],
            ['UNION combines result sets.', 'UNION deletes data.'],
            ['Views are virtual tables.', 'Views store physical data.'],
            ['Stored procedures run on server.', 'Stored procedures run in browser.'],
            ['Triggers execute automatically.', 'Triggers must be manually executed.'],
            ['ACID ensures reliability.', 'ACID reduces consistency.'],
            ['Index speeds search.', 'Index slows search always.'],
            ['Composite key uses multiple columns.', 'Composite key uses single column only.'],
            ['Unique constraint prevents duplicates.', 'Unique allows duplicates.'],
            ['Joins combine tables.', 'Joins split tables.'],
            ['LIMIT restricts rows.', 'LIMIT deletes rows.'],
            ['OFFSET skips rows.', 'OFFSET sorts rows.'],
            ['EXPLAIN analyzes query.', 'EXPLAIN executes query.'],
            ['DISTINCT removes duplicates.', 'DISTINCT adds duplicates.'],

            // ===== PHP INTERMEDIATE (25 pairs) =====
            ['OOP improves code reuse.', 'OOP prevents reuse.'],
            ['Encapsulation hides data.', 'Encapsulation exposes all data.'],
            ['Inheritance allows reuse.', 'Inheritance blocks reuse.'],
            ['Polymorphism allows flexibility.', 'Polymorphism restricts methods.'],
            ['Interfaces define contracts.', 'Interfaces contain full logic.'],
            ['Abstract classes cannot be instantiated.', 'Abstract classes can be instantiated directly.'],
            ['Traits enable code reuse.', 'Traits replace classes completely.'],
            ['Exception handling improves stability.', 'Exceptions crash system always.'],
            ['try-catch handles errors.', 'try-catch ignores errors.'],
            ['PDO supports prepared statements.', 'PDO does not support prepared statements.'],
            ['Prepared statements prevent SQL injection.', 'Prepared statements cause SQL injection.'],
            ['Sessions store server data.', 'Sessions store client-only data.'],
            ['Cookies stored in browser.', 'Cookies stored in server memory.'],
            ['CSRF tokens improve security.', 'CSRF tokens reduce security.'],
            ['Password hashing improves security.', 'Plain passwords are secure.'],
            ['file_get_contents reads files.', 'file_get_contents deletes files.'],
            ['include_once prevents duplication.', 'include_once repeats code.'],
            ['require_once ensures single load.', 'require_once loads multiple times always.'],
            ['Namespaces avoid conflicts.', 'Namespaces create conflicts.'],
            ['Autoloading loads classes automatically.', 'Autoloading disables classes.'],
            ['Magic methods start with __.', 'Magic methods start with @@.'],
            ['__construct initializes object.', '__construct deletes object.'],
            ['__destruct runs at end.', '__destruct runs at start.'],
            ['Static methods belong to class.', 'Static methods belong to instance only.'],
            ['Late static binding uses static keyword.', 'Late static binding uses only self keyword.'],
        ];
    }

    private function mcqs(): array
    {
        return [
            // ===== MYSQL (50 MCQ) =====
            ['Which join returns matching rows?', ['INNER JOIN','LEFT JOIN','RIGHT JOIN','FULL'], 'INNER JOIN'],
            ['Which join returns all left rows?', ['LEFT JOIN','INNER JOIN','RIGHT JOIN','FULL'], 'LEFT JOIN'],
            ['Which improves performance?', ['INDEX','JOIN','DELETE','DROP'], 'INDEX'],
            ['Which ensures atomicity?', ['Transaction','Index','View','Join'], 'Transaction'],
            ['Which saves changes?', ['COMMIT','ROLLBACK','SAVE','END'], 'COMMIT'],
            ['Which cancels changes?', ['ROLLBACK','COMMIT','SAVE','END'], 'ROLLBACK'],
            ['Which prevents duplicates?', ['UNIQUE','NULL','INDEX','DEFAULT'], 'UNIQUE'],
            ['Which groups rows?', ['GROUP BY','ORDER BY','WHERE','JOIN'], 'GROUP BY'],
            ['Which filters grouped?', ['HAVING','WHERE','JOIN','GROUP'], 'HAVING'],
            ['Which combines results?', ['UNION','JOIN','WHERE','GROUP'], 'UNION'],
            ['Which is virtual table?', ['VIEW','TABLE','INDEX','JOIN'], 'VIEW'],
            ['Which runs automatically?', ['TRIGGER','VIEW','JOIN','INDEX'], 'TRIGGER'],
            ['Which executes logic?', ['PROCEDURE','VIEW','INDEX','JOIN'], 'PROCEDURE'],
            ['Which analyzes query?', ['EXPLAIN','RUN','EXECUTE','CHECK'], 'EXPLAIN'],
            ['Which removes duplicates?', ['DISTINCT','GROUP','ORDER','JOIN'], 'DISTINCT'],
            ['Which limits rows?', ['LIMIT','OFFSET','WHERE','JOIN'], 'LIMIT'],
            ['Which skips rows?', ['OFFSET','LIMIT','WHERE','JOIN'], 'OFFSET'],
            ['Which enforces relation?', ['FOREIGN KEY','PRIMARY','INDEX','VIEW'], 'FOREIGN KEY'],
            ['Which uses multiple columns?', ['Composite Key','Primary','Unique','Index'], 'Composite Key'],
            ['Which ensures consistency?', ['ACID','JOIN','INDEX','VIEW'], 'ACID'],

            // ===== PHP (50 MCQ) =====
            ['OOP stands for?', ['Object Oriented Programming','Only One Program','Open Object Process','None'], 'Object Oriented Programming'],
            ['Which keyword inheritance?', ['extends','include','use','import'], 'extends'],
            ['Which defines contract?', ['interface','class','trait','object'], 'interface'],
            ['Which cannot instantiate?', ['abstract','class','trait','interface'], 'abstract'],
            ['Which reuse code?', ['trait','class','object','function'], 'trait'],
            ['Which handles errors?', ['try-catch','if','loop','switch'], 'try-catch'],
            ['Which prevents SQL injection?', ['Prepared Statement','Query','Join','Select'], 'Prepared Statement'],
            ['PDO stands for?', ['PHP Data Objects','Private Data Object','Public Data Output','None'], 'PHP Data Objects'],
            ['Which hashes password?', ['password_hash','md5','sha1','crypt'], 'password_hash'],
            ['Which verifies password?', ['password_verify','hash','check','validate'], 'password_verify'],
            ['Which loads file once?', ['require_once','include','require','load'], 'require_once'],
            ['Which avoids duplicate include?', ['include_once','require','include','load'], 'include_once'],
            ['Which organizes code?', ['namespace','class','trait','function'], 'namespace'],
            ['Which autoloads?', ['spl_autoload_register','autoload','include','require'], 'spl_autoload_register'],
            ['Which magic method constructor?', ['__construct','__init','__start','__new'], '__construct'],
            ['Which destructor?', ['__destruct','__end','__delete','__stop'], '__destruct'],
            ['Which static access?', ['::','->','.','&'], '::'],
            ['Which object access?', ['->','::','.','&'], '->'],
            ['Which session start?', ['session_start','start','begin','init'], 'session_start'],
            ['Which cookie set?', ['setcookie','cookie','addcookie','make'], 'setcookie'],
        ];
    }
}
