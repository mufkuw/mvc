<?php

//register smarty plugins
smarty_plugnis_setups();

function smarty_plugnis_setups()
{
	$view = SmartyView::instance();
	
	$view->registerPlugin('function'	,'l'						,'_SmartyView_LanguageTranslationProvider_Function');
	$view->registerPlugin('function'	,'hook'						,'_SmartyView_Hook_Function');
	$view->registerPlugin('function'	,'inc'						,'_SmartyView_Include_Function');
	
	$view->registerPlugin('function'	,'hidden'					,'_SmartyView_Hidden_Function');
	
	$view->registerPlugin('modifier'	,'null'						,'_SmartyView_Null_Modifier');

	
	//$view->registerPlugin('function'	,'html_widget'				,'_SmartyView_Widget_Function');
	//$view->registerPlugin('function'	,'alert'					,'_SmartyView_Widget_Alert_Function');
	
	SmartyViewWidgets::instance()->register($view);
}


function _SmartyView_Hook_Function($param,&$smarty)
{
	if(!isset($param['event'])) return '';
	
	$param['params'] = $param;
	$event = $param['event'];
	
	return Hook::execute($event,$param,function($success,$hook_results) use ($event){
		$html = '';
		foreach ($hook_results as $result)
		{
			if(!$result['error'])
				$html.=$result['result'];
		}
		
		return	"<!--Rendering Hook $event-->"
				.$html
				."<!--End Rendering Hook $event-->";
		
	});
	
}
function _SmartyView_LanguageTranslationProvider_Function($param,&$smarty)
{
	return Translations::translate($param['s']);
}
function _SmartyView_Include_Function($params,&$smarty)
{
	
	$view = Context::instance()->view;
	$theme = Context::instance()->theme;
	
	$path = $theme->getTemplate($params['template']);

	if(!$path) return	'';
	
	$tpl = $view->createTemplate($path);
	
	foreach ($params as $key=>$param)
		$tpl->assign($key,$param);
	
	$html = $tpl->render();
	
	foreach ($params as $key=>$param)
		$view->$key = null;

	return $html;
	
}


function _SmartyView_Null_Modifier(&$string, $value="")
{
	if(isset($string)) return $string; else return $value;
	   
}

function _SmartyView_Hidden_Function($params,&$smarty)
{
	$html='';
	foreach ($params as $key=>$value)
	{
		if(isset($params['model'])) 
			$name_key = $params['model'] . '['. $key .']';
		else
			$name_key = $key;
		
		$html.="<INPUT type=hidden id=$key name=$name_key value=$value>";
	}
	
	return $html;
	
}