<?php
class woofiltersModel extends modelWpf {
	public function __construct() {
		$this->_setTbl('filters');
	}

	public function getFilterLabels($filter) {
		switch ($filter) {
			case 'SortBy':
				$labels = array(
					'default' => __('Default', WPF_LANG_CODE),
					'popularity' => __('Popularity', WPF_LANG_CODE),
					'rating' => __('Rating', WPF_LANG_CODE),
					'date' => __('Newness', WPF_LANG_CODE),
					'price' => __('Price: low to high', WPF_LANG_CODE),
					'price-desc' => __('Price: high to low', WPF_LANG_CODE),
					'rand' => __('Random', WPF_LANG_CODE),
					'title' => __('Name', WPF_LANG_CODE),
					);
				break;
			case 'InStock':
				$labels = array(
					'instock' => __('In Stock', WPF_LANG_CODE),
					'outofstock' => __('Out of Stock', WPF_LANG_CODE),
					'onbackorder' => __('On Backorder', WPF_LANG_CODE),
					);
				break;		
			case 'OnSale':
				$labels = array(
					'onsale' => __('On Sale', WPF_LANG_CODE)
					);
				break;
			case 'Category':
			case 'Tags':
			case 'Attribute':
			case 'Author':
				$labels = array(
					'search' => __('Search ...', WPF_LANG_CODE)
					);
				break;
			default:
				$labels = array();
				break;
		}
		return $labels;
	}

	public function save($data = array()){
		$id = isset($data['id']) ? ($data['id']) : false;

		$title = !empty($data['title']) ? ($data['title']) : date('Y-m-d-h-i-s');
		$data['title'] = $title;
		$duplicateId = isset($data['duplicateId']) ? ($data['duplicateId']) : false;
        //already created filter
        if( !empty($id) && !empty($title) ) {
            $data['id'] = (string)$id;
            $statusUpdate = $this->updateById( $data , $id );
            if($statusUpdate){
                return $id;
            }
        } else if( empty($id) && !empty($title) && empty($duplicateId) ) {  //empty filter
            $idInsert = $this->insert( $data );
            if($idInsert){
                if(empty($title)){
                    $title = (string)$idInsert;
                }
				$data['id'] = (string)$idInsert;
                $this->updateById( $data , $idInsert );
            }
            return $idInsert;
        } else if( empty($id) && !empty($title) && !empty($duplicateId) ) {  //duplicate filter
			$duplicateData = $this->getById($duplicateId);
			$settings = unserialize($duplicateData['setting_data']);
			$duplicateData['settings'] = $settings['settings'];
			$duplicateData['title'] = isset($title) ? $title : 'untitled';
			$duplicateData['id'] = '';
			$idInsert = $this->insert( $duplicateData );
            return $idInsert;
        }
		// else //empty title
        //     $this->pushError (__('Name can\'t be empty or more than 255 characters', WPF_LANG_CODE), 'title');
        return false;
    }
	protected function _dataSave($data, $update = false){
        $settings = isset($data['settings']) ? $data['settings'] : array();
		$data['settings']['css_editor'] = isset($settings['css_editor']) ? base64_encode($settings['css_editor']) : '';
		$data['settings']['js_editor'] = isset($settings['js_editor']) ? base64_encode($settings['js_editor']) : '';
		$data['settings']['filters']['order'] = isset($settings['filters']) && isset($settings['filters']['order']) ? stripslashes($settings['filters']['order']) : '';
		$notEdit = array('css_editor', 'js_editor', 'filters');
		foreach($data['settings'] as $key => $value) {
			if(!in_array($key, $notEdit) && is_string($value)) {
				$v = str_replace('"', '&quot;', str_replace('\"', '"', $value));
				$data['settings'][$key] = str_replace("'", '&#039;', str_replace("\'", "'", $v));
			}
		}
		$settingData = array('settings' => $data['settings']);
		$data['setting_data'] = serialize($settingData);
		return $data;
	}
}
