<?php

class MediaTypes {
	const JS = 'js';
	const CSS = 'css';
}

class Media extends Foundation
{
	private $media_files=[];
	private $scope = '';

	public function __construct($scope='')
	{
		$this->media_files['js']=[];
		$this->media_files['css']=[];
		$this->scope = $scope;
	}
	
	private function addMedia($type,$media,$validate=true,$module='')
	{
		
		$theme = $this->context()->theme;
		
		if($media)
		{
			if($type=='js')
			{
				$media_path = $this->getJs($media,$module);
			}
			else if($type=='css')
			{
				$media_path = $this->getCss($media,$module);
			}
			
			if(($validate && realpath(ROOT.$media_path)) || !$validate)
			{
				$this->media_files[$type][] = $media_path;
				$this->media_files[$type] = array_unique($this->media_files[$type]);
			}

			return true;

		}
	}
	
	public function addJs($media,$validate=true,$module='')
	{
		return $this->addMedia('js',$media,$validate,$module);
	}
	
	public function addCss($media,$validate=true,$module='')
	{
		return $this->addMedia('css',$media,$validate,$module);
	}
	
	private function renderMedia($type)
	{
		$media_template = [
				'js'=>JS_TEMPLATE
				,'css'=>CSS_TEMPLATE
			];
		
		$html='';
		
		foreach($this->media_files[$type] as $media)
		{
		    $html.= str_replace('{MEDIA}',$media,$media_template[$type]);
		}

		return	"<!--Start $this->scope ($type) -->"
		        .$html
		        ."<!--End $this->scope ($type) -->";
	}

	public function renderJs()
	{
		return $this->renderMedia('js');
	}
		
	public function renderCss()
	{
		return $this->renderMedia('css');
	}
	
	private function getMedia($type,$file,$module='')
	{

		$theme = Theme::instance();
		
		if(!$file) return $file;
		
		$search_paths =array();
		$search_paths[] = $file;
		$search_paths[] = $theme->getPath().''.$type.'/'.$file;
		$search_paths[] = '/'.str_replace(ROOT,'',MODULES).$module.'/'.$type.'/'.$file;
		
		foreach($search_paths as $path)
		{	
			if(realpath(ROOT.$path)) return $path;
			if(realpath(ROOT.$path.'.'.$type)) return $path.'.'.$type;
		}
		return $file;
	}

	public function getJs($file,$module='')
	{
		return $this->getMedia('js',$file,$module);
	}
	
	public function getCss($file,$module='')
	{
		return $this->getMedia('css',$file,$module);
	}


	
}


