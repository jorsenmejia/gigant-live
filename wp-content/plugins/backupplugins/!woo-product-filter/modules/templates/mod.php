<?php
class templatesWpf extends moduleWpf {
    protected $_styles = array();
	private $_cdnUrl = '';

	public function __construct($d) {
		parent::__construct($d);
		$this->getCdnUrl();	// Init CDN URL
	}
	public function getCdnUrl() {
		if(empty($this->_cdnUrl)) {
			if((int) frameWpf::_()->getModule('options')->get('use_local_cdn')) {
				$uploadsDir = wp_upload_dir( null, false );
				$this->_cdnUrl = $uploadsDir['baseurl']. '/'. WPF_CODE. '/';
				if(uriWpf::isHttps()) {
					$this->_cdnUrl = str_replace('http://', 'https://', $this->_cdnUrl);
				}
				dispatcherWpf::addFilter('externalCdnUrl', array($this, 'modifyExternalToLocalCdn'));
			} else {
				$this->_cdnUrl = (uriWpf::isHttps() ? 'https' : 'http'). '://supsystic-42d7.kxcdn.com/';
			}
		}
		return $this->_cdnUrl;
	}
	public function modifyExternalToLocalCdn( $url ) {
		$url = str_replace(
			array('https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css'),
			array($this->_cdnUrl. 'lib/font-awesome'),
			$url);
		return $url;
	}
    public function init() {
        if (is_admin()) {
			if($isAdminPlugOptsPage = frameWpf::_()->isAdminPlugOptsPage()) {
				$this->loadCoreJs();
				$this->loadAdminCoreJs();
				$this->loadCoreCss();
				$this->loadChosenSelects();
				frameWpf::_()->addScript('adminOptionsWpf', WPF_JS_PATH. 'admin.options.js', array(), false, true);
				add_action('admin_enqueue_scripts', array($this, 'loadMediaScripts'));
				add_action('init', array($this, 'connectAdditionalAdminAssets'));
			}
			// Some common styles - that need to be on all admin pages - be careful with them
			frameWpf::_()->addStyle('supsystic-for-all-admin-'. WPF_CODE, WPF_CSS_PATH. 'supsystic-for-all-admin.css');
		}
        parent::init();
    }
	public function connectAdditionalAdminAssets() {
		if(is_rtl()) {
			frameWpf::_()->addStyle('styleWpf-rtl', WPF_CSS_PATH. 'style-rtl.css');
		}
	}
	public function loadMediaScripts() {
		if(function_exists('wp_enqueue_media')) {
			wp_enqueue_media();
		}
	}
	public function loadAdminCoreJs() {
		frameWpf::_()->addScript('jquery-ui-dialog');
		frameWpf::_()->addScript('jquery-ui-slider');
		frameWpf::_()->addScript('wp-color-picker');
		frameWpf::_()->addScript('icheck', WPF_JS_PATH. 'icheck.min.js');
	}
	public function loadCoreJs() {
		static $loaded = false;
		if(!$loaded) {
			frameWpf::_()->addScript('jquery');

			frameWpf::_()->addScript('commonWpf', WPF_JS_PATH. 'common.js');
			frameWpf::_()->addScript('coreWpf', WPF_JS_PATH. 'core.js');

			$ajaxurl = admin_url('admin-ajax.php');
			$jsData = array(
				'siteUrl'					=> WPF_SITE_URL,
				'imgPath'					=> WPF_IMG_PATH,
				'cssPath'					=> WPF_CSS_PATH,
				'loader'					=> WPF_LOADER_IMG,
				'close'						=> WPF_IMG_PATH. 'cross.gif',
				'ajaxurl'					=> $ajaxurl,
				'options'					=> frameWpf::_()->getModule('options')->getAllowedPublicOptions(),
				'WPF_CODE'					=> WPF_CODE,
				//'ball_loader'				=> WPF_IMG_PATH. 'ajax-loader-ball.gif',
				//'ok_icon'					=> WPF_IMG_PATH. 'ok-icon.png',
				'jsPath'					=> WPF_JS_PATH,
			);
			if(is_admin()) {
				$jsData['isPro'] = frameWpf::_()->getModule('promo')->isPro();
				$jsData['mainLink'] = frameWpf::_()->getModule('promo')->getMainLink();
			}
			$jsData = dispatcherWpf::applyFilters('jsInitVariables', $jsData);
			frameWpf::_()->addJSVar('coreWpf', 'WPF_DATA', $jsData);
			$this->loadTooltipster();
			$loaded = true;
		}
	}
	public function loadTooltipster() {
		frameWpf::_()->addScript('tooltipster', $this->_cdnUrl. 'lib/tooltipster/jquery.tooltipster.min.js');
		frameWpf::_()->addStyle('tooltipster', $this->_cdnUrl. 'lib/tooltipster/tooltipster.css');
	}
	public function loadSlimscroll() {
		frameWpf::_()->addScript('jquery.slimscroll', $this->_cdnUrl. 'js/jquery.slimscroll.js');
	}
	public function loadCodemirror() {
		frameWpf::_()->addStyle('wpfCodemirror', $this->_cdnUrl. 'lib/codemirror/codemirror.css');
		frameWpf::_()->addStyle('codemirror-addon-hint', $this->_cdnUrl. 'lib/codemirror/addon/hint/show-hint.css');
		frameWpf::_()->addScript('wpfCodemirror', $this->_cdnUrl. 'lib/codemirror/codemirror.js');
		frameWpf::_()->addScript('codemirror-addon-show-hint', $this->_cdnUrl. 'lib/codemirror/addon/hint/show-hint.js');
		frameWpf::_()->addScript('codemirror-addon-xml-hint', $this->_cdnUrl. 'lib/codemirror/addon/hint/xml-hint.js');
		frameWpf::_()->addScript('codemirror-addon-html-hint', $this->_cdnUrl. 'lib/codemirror/addon/hint/html-hint.js');
		frameWpf::_()->addScript('codemirror-mode-xml', $this->_cdnUrl. 'lib/codemirror/mode/xml/xml.js');
		frameWpf::_()->addScript('codemirror-mode-javascript', $this->_cdnUrl. 'lib/codemirror/mode/javascript/javascript.js');
		frameWpf::_()->addScript('codemirror-mode-css', $this->_cdnUrl. 'lib/codemirror/mode/css/css.js');
		frameWpf::_()->addScript('codemirror-mode-htmlmixed', $this->_cdnUrl. 'lib/codemirror/mode/htmlmixed/htmlmixed.js');
	}
	public function loadCoreCss() {
		$this->_styles = array(
			'styleWpf'			=> array('path' => WPF_CSS_PATH. 'style.css', 'for' => 'admin'),
			'supsystic-uiWpf'	=> array('path' => WPF_CSS_PATH. 'supsystic-ui.css', 'for' => 'admin'),
			'dashicons'			=> array('for' => 'admin'),
			'bootstrap-alerts'	=> array('path' => WPF_CSS_PATH. 'bootstrap-alerts.css', 'for' => 'admin'),
			'icheck'			=> array('path' => WPF_CSS_PATH. 'jquery.icheck.css', 'for' => 'admin'),
			//'uniform'			=> array('path' => WPF_CSS_PATH. 'uniform.default.css', 'for' => 'admin'),
			'wp-color-picker'	=> array('for' => 'admin'),
		);
		foreach($this->_styles as $s => $sInfo) {
			if(!empty($sInfo['path'])) {
				frameWpf::_()->addStyle($s, $sInfo['path']);
			} else {
				frameWpf::_()->addStyle($s);
			}
		}
		$this->loadFontAwesome();
	}
	public function loadJqueryUi() {
		static $loaded = false;
		if(!$loaded) {
			frameWpf::_()->addStyle('jquery-ui', WPF_CSS_PATH. 'jquery-ui.min.css');
			frameWpf::_()->addStyle('jquery-ui.structure', WPF_CSS_PATH. 'jquery-ui.structure.min.css');
			frameWpf::_()->addStyle('jquery-ui.theme', WPF_CSS_PATH. 'jquery-ui.theme.min.css');
			frameWpf::_()->addStyle('jquery-slider', WPF_CSS_PATH. 'jquery-slider.css');
			$loaded = true;
		}
	}
	public function loadJqGrid() {
		static $loaded = false;
		if(!$loaded) {
			$this->loadJqueryUi();
			frameWpf::_()->addScript('jq-grid', $this->_cdnUrl. 'lib/jqgrid/jquery.jqGrid.min.js');
			frameWpf::_()->addStyle('jq-grid', $this->_cdnUrl. 'lib/jqgrid/ui.jqgrid.css');
			$langToLoad = utilsWpf::getLangCode2Letter();
			$availableLocales = array('ar','bg','bg1251','cat','cn','cs','da','de','dk','el','en','es','fa','fi','fr','gl','he','hr','hr1250','hu','id','is','it','ja','kr','lt','mne','nl','no','pl','pt','pt','ro','ru','sk','sr','sr','sv','th','tr','tw','ua','vi');
			if(!in_array($langToLoad, $availableLocales)) {
				$langToLoad = 'en';
			}
			frameWpf::_()->addScript('jq-grid-lang', $this->_cdnUrl. 'lib/jqgrid/i18n/grid.locale-'. $langToLoad. '.js');
			$loaded = true;
		}
	}
	public function loadFontAwesome() {
		frameWpf::_()->addStyle('font-awesomeWpf', dispatcherWpf::applyFilters('externalCdnUrl', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'));
	}
	public function loadChosenSelects() {
		frameWpf::_()->addStyle('jquery.chosen', $this->_cdnUrl. 'lib/chosen/chosen.min.css');
		frameWpf::_()->addScript('jquery.chosen', $this->_cdnUrl. 'lib/chosen/chosen.jquery.min.js');
	}
	public function loadDatePicker() {
		frameWpf::_()->addScript('jquery-ui-datepicker');
	}
	public function loadJqplot() {
		static $loaded = false;
		if(!$loaded) {
			$jqplotDir = $this->_cdnUrl. 'lib/jqplot/';

			frameWpf::_()->addStyle('jquery.jqplot', $jqplotDir. 'jquery.jqplot.min.css');

			frameWpf::_()->addScript('jplot', $jqplotDir. 'jquery.jqplot.min.js');
			frameWpf::_()->addScript('jqplot.canvasAxisLabelRenderer', $jqplotDir. 'jqplot.canvasAxisLabelRenderer.min.js');
			frameWpf::_()->addScript('jqplot.canvasTextRenderer', $jqplotDir. 'jqplot.canvasTextRenderer.min.js');
			frameWpf::_()->addScript('jqplot.dateAxisRenderer', $jqplotDir. 'jqplot.dateAxisRenderer.min.js');
			frameWpf::_()->addScript('jqplot.canvasAxisTickRenderer', $jqplotDir. 'jqplot.canvasAxisTickRenderer.min.js');
			frameWpf::_()->addScript('jqplot.highlighter', $jqplotDir. 'jqplot.highlighter.min.js');
			frameWpf::_()->addScript('jqplot.cursor', $jqplotDir. 'jqplot.cursor.min.js');
			frameWpf::_()->addScript('jqplot.barRenderer', $jqplotDir. 'jqplot.barRenderer.min.js');
			frameWpf::_()->addScript('jqplot.categoryAxisRenderer', $jqplotDir. 'jqplot.categoryAxisRenderer.min.js');
			frameWpf::_()->addScript('jqplot.pointLabels', $jqplotDir. 'jqplot.pointLabels.min.js');
			frameWpf::_()->addScript('jqplot.pieRenderer', $jqplotDir. 'jqplot.pieRenderer.min.js');
			$loaded = true;
		}
	}
	public function loadSortable() {
		static $loaded = false;
		if(!$loaded) {
			frameWpf::_()->addScript('jquery-ui-core');
			frameWpf::_()->addScript('jquery-ui-widget');
			frameWpf::_()->addScript('jquery-ui-mouse');

			frameWpf::_()->addScript('jquery-ui-draggable');
			frameWpf::_()->addScript('jquery-ui-sortable');
			$loaded = true;
		}
	}
	public function loadMagicAnims() {
		static $loaded = false;
		if(!$loaded) {
			frameWpf::_()->addStyle('magic.anim', $this->_cdnUrl. 'css/magic.min.css');
			$loaded = true;
		}
	}
	public function loadCssAnims() {
		static $loaded = false;
		if(!$loaded) {
			frameWpf::_()->addStyle('animate.styles', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.4.0/animate.min.css');
			$loaded = true;
		}
	}
	public function loadBootstrapSimple() {
		static $loaded = false;
		if(!$loaded) {
			frameWpf::_()->addStyle('bootstrap-simple', WPF_CSS_PATH. 'bootstrap-simple.css');
			$loaded = true;
		}
	}
	public function loadGoogleFont( $font ) {
		static $loaded = array();
		if(!isset($loaded[ $font ])) {
			frameWpf::_()->addStyle('google.font.'. str_replace(array(' '), '-', $font), 'https://fonts.googleapis.com/css?family='. urlencode($font));
			$loaded[ $font ] = 1;
		}
	}
	public function loadBxSlider() {
		static $loaded = false;
		if(!$loaded) {
			frameWpf::_()->addStyle('bx-slider', WPF_JS_PATH. 'bx-slider/jquery.bxslider.css');
			frameWpf::_()->addScript('bx-slider', WPF_JS_PATH. 'bx-slider/jquery.bxslider.min.js');
			$loaded = true;
		}
	}
}
