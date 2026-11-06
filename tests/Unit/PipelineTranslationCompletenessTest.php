<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PipelineTranslationCompletenessTest extends TestCase
{
    public function test_pipeline_editor_translation_keys_match_in_thai_english_and_chinese(): void
    {
        foreach (['pipeline_js', 'pipeline_detail', 'pipeline_editor', 'pipeline_page', 'crm_headings', 'crm_errors', 'auth_ui'] as $file) {
            $english = require dirname(__DIR__, 2)."/lang/en/{$file}.php";
            foreach (['th', 'zh'] as $locale) {
                $translated = require dirname(__DIR__, 2)."/lang/{$locale}/{$file}.php";
                $this->assertSame($this->keys($english), $this->keys($translated), "{$locale}/{$file}");
            }
        }
    }

    private function keys(array $values): array
    {
        $keys = [];
        foreach ($values as $key => $value) {
            $keys[$key] = is_array($value) ? $this->keys($value) : true;
        }

        return $keys;
    }
}
