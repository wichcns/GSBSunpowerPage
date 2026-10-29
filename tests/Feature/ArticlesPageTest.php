<?php

namespace Tests\Feature;

use Tests\TestCase;

class ArticlesPageTest extends TestCase
{
    public function test_blog_page_is_available_and_legacy_urls_redirect_to_it(): void
    {
        $this->get(route('blog'))
            ->assertOk()
            ->assertSee('Blog')
            ->assertSee('COMING SOON');

        $this->get('/articles.html')
            ->assertRedirect('/blog.html');
        $this->get('/insights.html')
            ->assertRedirect('/blog.html');
    }
}
