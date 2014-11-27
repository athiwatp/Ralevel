<?php

return array(

	'vendors' => array(
					
					'Illuminate/View/Compilers/BladeCompiler',
					'JayaInstitute/Auth',
					'JayaInstitute/Config',
					'JayaInstitute/Db',
					'JayaInstitute/Facade',
					'JayaInstitute/Hash',
					'JayaInstitute/Input',
					'JayaInstitute/Model',
					'JayaInstitute/Redirect',
					'JayaInstitute/Request',
					'JayaInstitute/Route',
					'JayaInstitute/Session',
					'JayaInstitute/Url',
					'JayaInstitute/View',
					'Ralevel/Ralevel',
					'Ralevel/Controller',
				),

	'folders' => array(

					COREPATH.'facades/',
					APPPATH.'models/',
				), 

	'files' => array(

					COREPATH.'vendor/JayaInstitute/helpers.php',
					APPPATH.'filters.php',
					APPPATH.'routers.php',
				),

);