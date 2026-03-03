<?php

namespace App\Providers;

use App\Models\Producto;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        Paginator::useBootstrapFive();

        View::composer('admin.layout', function ($view) {
            $count = Producto::where('activo', true)
                ->withSum('detalle_compras as stock_total', 'cantidad_restante')
                ->having('stock_total', '<=', 5)
                ->count();
            $view->with('stockBajoCount', $count);
        });
    }
}
