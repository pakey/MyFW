<?php
return array(
	'SITENAME'=>'PTCMS工作室',
	'CACHE_PREFIX'=>'ptcmsweb',

	'URL_RULES'=>array(
		'index.article.list'=>'/{dir}[/{key}][/{page}]',
	),

	'URL_ROUTER'=>array(
		'^(news|course)$'=>'index/article/list?module',
	),

);