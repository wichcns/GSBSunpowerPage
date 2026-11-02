<?php

use App\Http\Controllers\Admin\AdminAboutContentController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminBlogController;
use App\Http\Controllers\Admin\AdminClientLogoController;
use App\Http\Controllers\Admin\AdminContactEnquiryController;
use App\Http\Controllers\Admin\AdminHomeContentController;
use App\Http\Controllers\Admin\AdminProjectController;
use App\Http\Controllers\Admin\AdminSectionController;
use App\Http\Controllers\Admin\AdminSolutionController;
use App\Http\Controllers\Admin\AdminTestimonialController;
use App\Http\Controllers\PublicSite\AboutChartMediaController;
use App\Http\Controllers\PublicSite\AboutPageController;
use App\Http\Controllers\PublicSite\BlogMediaController;
use App\Http\Controllers\PublicSite\BlogPageController;
use App\Http\Controllers\PublicSite\ContactEnquiryController;
use App\Http\Controllers\PublicSite\HomePageController;
use App\Http\Controllers\PublicSite\ProjectPageController;
use App\Http\Controllers\PublicSite\SolutionPageController;
use App\Support\PublishedBlogContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website
|--------------------------------------------------------------------------
|
| The legacy .html URLs are deliberately retained during the migration.
| Existing bookmarks, search results, and links in the current front-end
| continue to work while the pages are now served by Laravel Blade views.
|
*/
Route::get('/', HomePageController::class)->name('home');

Route::get('/about.html', AboutPageController::class)->name('about');
Route::get('/about-chart-media/{path}', [AboutChartMediaController::class, 'show'])->where('path', '.*')->name('about-chart.media');
Route::get('/blog.html', [BlogPageController::class, 'index'])->name('blog');
Route::get('/blog/{post}', [BlogPageController::class, 'show'])->name('blog.show');
Route::get('/site-status', fn (PublishedBlogContent $publishedBlogContent) => response()->json([
    'blog_available' => $publishedBlogContent->isAvailable(),
]))->name('site.navigation');
Route::get('/media/{path}', [BlogMediaController::class, 'show'])->where('path', '.*')->name('media.public');
Route::get('/solutions.html', SolutionPageController::class)->name('solutions');
Route::get('/solar-ppa.html', fn () => app(ProjectPageController::class)->show('ppa'))->name('solar-ppa');
Route::get('/epc.html', fn () => app(ProjectPageController::class)->show('epc'))->name('epc');
Route::get('/ev-charging.html', fn () => app(ProjectPageController::class)->show('ev'))->name('ev-charging');
Route::view('/contact.html', 'pages.contact')->name('contact');
Route::post('/contact-enquiries', [ContactEnquiryController::class, 'store'])->middleware('throttle:5,1')->name('contact-enquiries.store');

// Retained only for existing legacy links. These links are removed from the
// current navigation by the front-end script.
Route::view('/approach.html', 'pages.approach')->name('approach');
Route::view('/projects.html', 'pages.projects')->name('projects');
Route::redirect('/articles.html', '/blog.html')->name('articles');
Route::redirect('/insights.html', '/blog.html')->name('insights');

Route::redirect('/index.html', '/');

// Clean URLs can be introduced immediately without breaking the legacy URLs.
Route::redirect('/about', '/about.html');
Route::redirect('/blog', '/blog.html');
Route::redirect('/articles', '/blog.html');
Route::redirect('/solutions', '/solutions.html');
Route::redirect('/solar-ppa', '/solar-ppa.html');
Route::redirect('/epc', '/epc.html');
Route::redirect('/ev-charging', '/ev-charging.html');
Route::redirect('/contact', '/contact.html');

/*
|--------------------------------------------------------------------------
| Administration
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware('admin.locale')->group(function (): void {
    Route::post('/language/{locale}', function (Request $request, string $locale) {
        abort_unless(in_array($locale, ['th', 'en', 'zh'], true), 404);
        $request->session()->put('admin_locale', $locale);

        return back();
    })->whereIn('locale', ['th', 'en', 'zh'])->name('language');
    Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'store'])->middleware('throttle:6,1')->name('login.store');

    Route::middleware('admin.demo')->group(function (): void {
        Route::get('/', [AdminSectionController::class, 'websiteContent'])->name('dashboard');
        Route::get('/projects/{type}/create', [AdminProjectController::class, 'create'])->whereIn('type', ['ppa', 'epc', 'ev'])->name('projects.create');
        Route::post('/projects/{type}', [AdminProjectController::class, 'store'])->whereIn('type', ['ppa', 'epc', 'ev'])->name('projects.store');
        Route::get('/projects/{type}/{project}', [AdminProjectController::class, 'show'])->whereIn('type', ['ppa', 'epc', 'ev'])->whereNumber('project')->name('projects.show');
        Route::get('/projects/{type}/{project}/edit', [AdminProjectController::class, 'edit'])->whereIn('type', ['ppa', 'epc', 'ev'])->whereNumber('project')->name('projects.edit');
        Route::put('/projects/{type}/{project}', [AdminProjectController::class, 'update'])->whereIn('type', ['ppa', 'epc', 'ev'])->whereNumber('project')->name('projects.update');
        Route::patch('/projects/{type}/{project}/publication', [AdminProjectController::class, 'togglePublication'])->whereIn('type', ['ppa', 'epc', 'ev'])->whereNumber('project')->name('projects.publication');
        Route::delete('/projects/{type}/{project}', [AdminProjectController::class, 'destroy'])->whereIn('type', ['ppa', 'epc', 'ev'])->whereNumber('project')->name('projects.destroy');
        Route::get('/projects/{type}', [AdminSectionController::class, 'projects'])
            ->whereIn('type', ['ppa', 'epc', 'ev'])
            ->name('projects');
        Route::redirect('/website-content', '/admin')->name('website-content');
        Route::get('/website-content/home-hero', [AdminHomeContentController::class, 'editHero'])->name('home-content.hero.edit');
        Route::put('/website-content/home-hero', [AdminHomeContentController::class, 'updateHero'])->name('home-content.hero.update');
        Route::get('/website-content/about-chart', [AdminAboutContentController::class, 'editChart'])->name('about-content.chart.edit');
        Route::put('/website-content/about-chart', [AdminAboutContentController::class, 'updateChart'])->name('about-content.chart.update');
        Route::get('/website-content/client-logos', [AdminClientLogoController::class, 'index'])->name('client-logos.index');
        Route::post('/website-content/client-logos', [AdminClientLogoController::class, 'store'])->name('client-logos.store');
        Route::delete('/website-content/client-logos/{clientLogo}', [AdminClientLogoController::class, 'destroy'])->name('client-logos.destroy');
        Route::get('/website-content/blog', [AdminBlogController::class, 'index'])->name('blog.index');
        Route::get('/website-content/blog/posts/create', [AdminBlogController::class, 'createPost'])->name('blog.posts.create');
        Route::post('/website-content/blog/posts', [AdminBlogController::class, 'storePost'])->name('blog.posts.store');
        Route::get('/website-content/blog/posts/{post}/edit', [AdminBlogController::class, 'editPost'])->name('blog.posts.edit');
        Route::put('/website-content/blog/posts/{post}', [AdminBlogController::class, 'updatePost'])->name('blog.posts.update');
        Route::delete('/website-content/blog/posts/{post}', [AdminBlogController::class, 'destroyPost'])->name('blog.posts.destroy');
        Route::get('/website-content/blog/videos/create', [AdminBlogController::class, 'createVideo'])->name('blog.videos.create');
        Route::post('/website-content/blog/videos', [AdminBlogController::class, 'storeVideo'])->name('blog.videos.store');
        Route::get('/website-content/blog/videos/{video}/edit', [AdminBlogController::class, 'editVideo'])->name('blog.videos.edit');
        Route::put('/website-content/blog/videos/{video}', [AdminBlogController::class, 'updateVideo'])->name('blog.videos.update');
        Route::delete('/website-content/blog/videos/{video}', [AdminBlogController::class, 'destroyVideo'])->name('blog.videos.destroy');
        Route::get('/website-content/testimonials', [AdminTestimonialController::class, 'index'])->name('testimonials.index');
        Route::get('/website-content/testimonials/{testimonial}/edit', [AdminTestimonialController::class, 'edit'])->name('testimonials.edit');
        Route::put('/website-content/testimonials/{testimonial}', [AdminTestimonialController::class, 'update'])->name('testimonials.update');
        Route::get('/website-settings', [AdminSectionController::class, 'websiteSettings'])->name('website-settings');
        Route::get('/careers', [AdminSectionController::class, 'careers'])->name('careers');
        Route::get('/contact-enquiries', [AdminContactEnquiryController::class, 'index'])->name('contact-enquiries');
        Route::get('/contact-enquiries/export', [AdminContactEnquiryController::class, 'export'])->name('contact-enquiries.export');
        Route::get('/contact-enquiries/{contactEnquiry}', [AdminContactEnquiryController::class, 'show'])->name('contact-enquiries.show');
        Route::patch('/contact-enquiries/{contactEnquiry}', [AdminContactEnquiryController::class, 'update'])->name('contact-enquiries.update');
        Route::resource('solutions', AdminSolutionController::class)->except('show');
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
    });
});

require __DIR__.'/crm.php';
