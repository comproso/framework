<?php

/**
 *	Comproso Framework.
 *
 *	This program is free software:
 *	you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation,
 *	either version 3 of the License, or (at your option) any later version.
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY;
 *	without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *	See the GNU Affero General Public License for more details.
 *	You should have received a copy of the GNU Affero General Public License along with this program.
 *	If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright License Copyright (C) 2016 Thiemo Kunze <hallo (at) wangaz (dot) com>
 * @license AGPL-3.0
 *
 */

namespace Comproso\Framework;

use Illuminate\Support\ServiceProvider;

class ComprosoFrameworkServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
	    $this->loadViewsFrom(base_path('resources/views/vendor/comproso/framework'), 'comproso');
	    $this->loadTranslationsFrom(base_path('resources/lang/vendor/comproso/framework'), 'comproso');

        // publish migrations
        $this->publishes([
	        __DIR__.'/database/migrations' => base_path('database/migrations'),
	    ], 'migrations');

	    // publish views
        $this->publishes([
	        __DIR__.'/resources/views' => base_path('resources/views/vendor/comproso/framework'),
	    ], 'views');

	    // publish lang
	    $this->publishes([
	        __DIR__.'/resources/lang' => base_path('resources/lang/vendor/comproso/framework')
	    ], "translations");

	    // publish assets
	    $this->publishes([
	        __DIR__.'/resources/assets' => public_path('vendor/comproso/framework')
	    ], "assets");
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
