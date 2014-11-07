<?php

require 'framework/core/start.php';

echo Maen::find(10);
View::make('layout.wow', ['menus' => ['satu', 'dua']]);