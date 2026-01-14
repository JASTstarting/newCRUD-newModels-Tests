<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    public function boot(): void
    {
        // Если используете Bootstrap 5
        Paginator::useBootstrapFive();

        // Для единообразного API без "data" обёртки, если будете JsonResource:
        JsonResource::withoutWrapping();

        // В режиме разработки — строгие проверки Eloquent:
        if (! $this->app->isProduction()) {
            // Помогает поймать N+1 (ленивые загрузки без явного разрешения)
            Model::preventLazyLoading();

            // Предотвращает молчаливое отбрасывание атрибутов, которых нет в fillable/guarded
            Model::preventSilentlyDiscardingAttributes();

            // Ошибка при доступе к несуществующим атрибутам модели
            Model::preventAccessingMissingAttributes();
        }
    }
}
