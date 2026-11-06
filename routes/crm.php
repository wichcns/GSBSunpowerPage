<?php

use App\Crm\Http\Controllers\Admin\ActivityController;
use App\Crm\Http\Controllers\Admin\ContractsController;
use App\Crm\Http\Controllers\Admin\DashboardController;
use App\Crm\Http\Controllers\Admin\EngineeringController;
use App\Crm\Http\Controllers\Admin\GlobalSearchController;
use App\Crm\Http\Controllers\Admin\IntegrationController;
use App\Crm\Http\Controllers\Admin\ManualController;
use App\Crm\Http\Controllers\Admin\MyWorkController;
use App\Crm\Http\Controllers\Admin\NotificationController;
use App\Crm\Http\Controllers\Admin\PipelineAttachmentController;
use App\Crm\Http\Controllers\Admin\PipelineBusinessController;
use App\Crm\Http\Controllers\Admin\PipelineController;
use App\Crm\Http\Controllers\Admin\PipelineStageController;
use App\Crm\Http\Controllers\Admin\PipelineVisitController;
use App\Crm\Http\Controllers\Admin\ReportController;
use App\Crm\Http\Controllers\Admin\SiteController;
use App\Crm\Http\Controllers\Admin\SiteOwnershipController;
use App\Crm\Http\Controllers\Admin\TeamWorkController;
use App\Crm\Http\Controllers\Admin\UnifiedLeadController;
use App\Crm\Http\Controllers\Admin\UserManagementController;
use App\Crm\Http\Controllers\LineWebhookController;
use App\Crm\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['crm.database', 'crm.locale'])->prefix('crm')->name('crm.')->group(function () {
    Route::get('/', fn () => redirect()->route(Auth::guard('crm')->check() ? 'crm.admin.dashboard' : 'crm.login'));
    Route::post('/webhooks/line', LineWebhookController::class)->name('webhooks.line');

    Route::middleware('auth:crm')->prefix('admin')->name('admin.')->group(function () {
        Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');
        Route::get('/', [DashboardController::class, 'index'])->middleware('crm.permission:dashboard.view')->name('dashboard');
        Route::get('/search', [GlobalSearchController::class, 'index'])->name('search');
        Route::get('/manuals', [ManualController::class, 'index'])->name('manuals.index');
        Route::get('/manuals/{language}/file', [ManualController::class, 'show'])->whereIn('language', ['th', 'zh'])->name('manuals.show');
        Route::get('/my-work', [MyWorkController::class, 'index'])->middleware('crm.permission:work.view')->name('my-work');
        Route::get('/my-work/items', [MyWorkController::class, 'items'])->middleware('crm.permission:work.view')->name('my-work.items');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::post('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
        Route::get('/team-work', [TeamWorkController::class, 'index'])->middleware('crm.permission:team.view')->name('team-work');
        Route::get('/ownership', [SiteOwnershipController::class, 'index'])->middleware('crm.permission:ownership.manage')->name('ownership.index');
        Route::post('/ownership/assign', [SiteOwnershipController::class, 'assign'])->middleware('crm.permission:ownership.manage')->name('ownership.assign');
        Route::get('/sites', [SiteController::class, 'index'])->middleware('crm.permission:sites.view')->name('sites.index');
        Route::post('/sites', [SiteController::class, 'store'])->middleware('crm.permission:pipeline.view')->name('sites.store');
        Route::post('/sites/{site}/opportunity', [SiteController::class, 'createOpportunity'])->middleware(['crm.permission:sites.write', 'crm.permission:pipeline.view'])->name('sites.opportunities.store');
        Route::get('/sites/{site}', [SiteController::class, 'show'])->middleware('crm.permission:pipeline.view')->name('sites.show');
        Route::patch('/sites/{site}', [SiteController::class, 'update'])->middleware('crm.permission:pipeline.view')->name('sites.update');
        Route::get('/pipeline', [PipelineController::class, 'index'])->middleware('crm.permission:pipeline.view')->name('pipeline.index');
        Route::delete('/pipeline/{sourceId}', [PipelineController::class, 'destroy'])->middleware(['crm.permission:pipeline.details', 'crm.assigned:pipeline'])->name('pipeline.destroy');
        Route::get('/pipeline/{sourceId}', [PipelineController::class, 'show'])->middleware(['crm.permission:pipeline.view', 'crm.assigned:pipeline'])->name('pipeline.show');
        Route::patch('/pipeline/{sourceId}/stage', [PipelineStageController::class, 'update'])->middleware(['crm.permission:pipeline.stage', 'crm.assigned:pipeline'])->name('pipeline.stage.update');
        Route::patch('/pipeline/{sourceId}/details', [UnifiedLeadController::class, 'update'])->middleware(['crm.permission:pipeline.details', 'crm.assigned:pipeline'])->name('pipeline.details.update');
        Route::patch('/pipeline/{sourceId}/business', [PipelineBusinessController::class, 'update'])->middleware(['crm.permission:pipeline.business', 'crm.assigned:pipeline'])->name('pipeline.business.update');
        Route::post('/pipeline/{sourceId}/visits', [PipelineVisitController::class, 'store'])->middleware(['crm.permission:pipeline.log', 'crm.assigned:pipeline'])->name('pipeline.visits.store');
        Route::patch('/pipeline/{sourceId}/visits/{visit}', [PipelineVisitController::class, 'update'])->middleware(['crm.permission:pipeline.log', 'crm.assigned:pipeline'])->name('pipeline.visits.update');
        Route::post('/pipeline/{sourceId}/attachments', [PipelineAttachmentController::class, 'store'])->middleware(['crm.permission:pipeline.log', 'crm.assigned:pipeline'])->name('pipeline.attachments.store');
        Route::patch('/pipeline/{sourceId}/attachments/{attachment}', [PipelineAttachmentController::class, 'replace'])->middleware(['crm.permission:pipeline.log', 'crm.assigned:pipeline'])->name('pipeline.attachments.replace');
        Route::delete('/pipeline/{sourceId}/attachments/{attachment}', [PipelineAttachmentController::class, 'destroy'])->middleware(['crm.permission:pipeline.log', 'crm.assigned:pipeline'])->name('pipeline.attachments.destroy');
        Route::get('/pipeline/{sourceId}/attachments/{attachment}', [PipelineAttachmentController::class, 'show'])->middleware(['crm.permission:pipeline.view', 'crm.assigned:pipeline'])->name('pipeline.attachments.show');
        Route::get('/contracts', [ContractsController::class, 'index'])->middleware('crm.permission:contracts.view')->name('contracts.index');
        Route::patch('/contracts/{sourceId}/terms', [ContractsController::class, 'updateTerms'])->middleware(['crm.permission:contracts.view', 'crm.permission:pipeline.business'])->name('contracts.terms.update');
        Route::get('/contracts/{sourceId}', [ContractsController::class, 'show'])->middleware('crm.permission:contracts.view')->name('contracts.show');
        Route::get('/engineering', [EngineeringController::class, 'index'])->middleware('crm.permission:engineering.view')->name('engineering.index');
        Route::get('/engineering/{sourceId}', [EngineeringController::class, 'show'])->middleware(['crm.permission:engineering.view', 'crm.assigned:engineering'])->name('engineering.show');
        Route::patch('/engineering/{sourceId}/stage', [EngineeringController::class, 'updateStage'])->middleware('crm.permission:engineering.manage')->name('engineering.stage.update');
        Route::patch('/engineering/{sourceId}/survey', [EngineeringController::class, 'saveSurvey'])->middleware('crm.permission:engineering.manage')->name('engineering.survey.update');
        Route::post('/engineering/{sourceId}/survey/complete', [EngineeringController::class, 'completeSurvey'])->middleware(['crm.permission:engineering.log', 'crm.assigned:engineering,survey'])->name('engineering.survey.complete');
        Route::post('/engineering/{sourceId}/construction/start', [EngineeringController::class, 'startConstruction'])->middleware('crm.permission:engineering.manage')->name('engineering.construction.start');
        Route::patch('/engineering/{sourceId}/construction', [EngineeringController::class, 'saveConstruction'])->middleware('crm.permission:engineering.manage')->name('engineering.construction.update');
        Route::post('/engineering/{sourceId}/construction/updates', [EngineeringController::class, 'addConstructionUpdate'])->middleware(['crm.permission:engineering.log', 'crm.assigned:engineering,construction'])->name('engineering.construction.updates.store');
        Route::get('/activities', [ActivityController::class, 'index'])->middleware('crm.permission:activities.view')->name('activities.index');
        Route::get('/reports', [ReportController::class, 'index'])->middleware('crm.permission:reports.view')->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->middleware('crm.permission:reports.view')->name('reports.export');
        Route::get('/integrations', [IntegrationController::class, 'index'])->middleware('crm.permission:system.manage')->name('integrations.index');
        Route::post('/integrations/line/confirm', [IntegrationController::class, 'confirmLineGroup'])->middleware('crm.permission:system.manage')->name('integrations.line.confirm');
        Route::post('/integrations/line/test', [IntegrationController::class, 'testLine'])->middleware('crm.permission:system.manage')->name('integrations.line.test');
        Route::post('/integrations/line/settings', [IntegrationController::class, 'updateLineSettings'])->middleware('crm.permission:system.manage')->name('integrations.line.settings');
        Route::post('/integrations/line/outbox/{outbox}/retry', [IntegrationController::class, 'retryLineOutbox'])->middleware('crm.permission:system.manage')->name('integrations.line.outbox.retry');
        Route::get('/settings', [UserManagementController::class, 'index'])->middleware('crm.permission:system.manage')->name('settings.index');
        Route::post('/settings/users', [UserManagementController::class, 'store'])->middleware('crm.permission:system.manage')->name('settings.users.store');
        Route::patch('/settings/users/{user}', [UserManagementController::class, 'update'])->middleware('crm.permission:system.manage')->name('settings.users.update');
    });

    require __DIR__.'/crm-auth.php';

});
