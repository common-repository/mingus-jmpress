<?php
class MingusJmpress_Admin{
	private $form_attributes;
	public function __construct() {
		if ( is_admin() ) {
			add_action('admin_menu', array( $this, 'add_plugin_page'));
			wp_register_style( 'adminStylesheet', plugins_url('admin.css', __FILE__) );
			//wp_enqueue_script( 'jquery' );
			/*
			$this->form_attributes = Verigir_Tools::form_attributes();
			$this->project_attributes = Verigir_Tools::project_attributes();
			//$this->applicant_attributes = ADK_Tools::get_applicant_attributes();
			
			
			
			//add_action('admin_footer-post.php', 'jc_append_post_status_list');
			
			
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-draggable' );
			wp_enqueue_script( 'jquery-ui-resizable' );
			//wp_enqueue_script( 'jquery-ui-core' );
			//wp_enqueue_script( 'jquery-ui-core' );
			global $wp_scripts;
			$ui = $wp_scripts->query('jquery-ui-core');
			$protocol = is_ssl() ? 'https' : 'http';
		    $url = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
		    wp_enqueue_style('jquery-ui-smoothness', $url, false, null);

			
			
			*/
		}
	}
	function add_plugin_page(){
		add_menu_page(
             'Mingus Jmpress', 
             'MingusJmpress', 
             'edit_others_posts', 
             'mingusjmpress', 
             array( $this, 'create_admin_page' ), 
             ''
        );
	}
	function save_slides(){
		$page = get_post($_REQUEST['id']);
		$jmKeys = MingusJmpress_Tools::jmKeys();
		$jmStyleKeys = MingusJmpress_Tools::jmStyleKeys();

		update_post_meta($page->ID,'jmpress-background-color',$_POST['background-color']);
		update_post_meta($page->ID,'jmpress-font-color',$_POST['font-color']);
		update_post_meta($page->ID,'jmpress-style',$_POST['extra-style']);
		

		$args = array(
			'posts_per_page'   => -1,
			//'offset'           => 0,
			'orderby'          => 'menu_order',
			'order'            => 'ASC',
			'post_type'		   => 'page',
			'post_parent'      => $page->ID,
			'post_status'      => 'publish');
		$pages = get_posts($args);
		foreach($pages as $page){
			
			update_post_meta($page->ID,'jmpress-class',$_POST[$page->ID.'-class']);
			foreach ($jmKeys as $key) {
				update_post_meta($page->ID,'jmpress-'.$key,$_POST[$page->ID.'-'.$key]);
			}
			foreach ($jmStyleKeys as $key) {
				update_post_meta($page->ID,'jmpress-'.$key,$_POST[$page->ID.'-'.$key]);
			}
		}
	}
	function create_admin_page(){
		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'adminStylesheet' );
		$html = new MingusHtml();
		echo $html->h(1,'Mingus Jmpress');
	    
		if($_REQUEST['id']>0){
			if($_POST['action']=='save'){
				$this->save_slides();
			}

			$page = get_post($_REQUEST['id']);
			echo $html->h(2,$page->post_title);

			$parentMeta = get_post_meta($page->ID);
			$ulHtml = $html->li(
				$html->div(
					$html->div(
						$html->gen('label','background color').
						$html->input('background-color','text',$parentMeta['jmpress-background-color'][0]),
						array('class'=>'cell')).
					$html->div(
						$html->gen('label','font color').
						$html->input('font-color','text',$parentMeta['jmpress-font-color'][0]),
						array('class'=>'cell')).
					$html->div(
						$html->gen('label','extra style').
						$html->input('extra-style','text',$parentMeta['jmpress-style'][0]),
						array('class'=>'cell')),
					array('class'=>'form-row'))
				);
			$jmKeys = MingusJmpress_Tools::jmKeys();
			$jmStyleKeys = MingusJmpress_Tools::jmStyleKeys();

			$args = array(
				'posts_per_page'   => -1,
				//'offset'           => 0,
				'orderby'          => 'menu_order',
				'order'            => 'ASC',
				'post_type'		   => 'page',
				'post_parent'      => $page->ID,
				'post_status'      => 'publish');
			$pages = get_posts($args);
			$preview = '';
			foreach($pages as $page){
				$meta = get_post_meta($page->ID);
				$liHtml = $html->h(3,$page->ID.' - '.$page->post_title);
				$preview .= $html->div($page->ID.' - '.$page->post_title,array('class'=>'preview-page','id'=>'pp-'.$page->ID));
				$divHtml = $html->div(
					$html->gen('label','class').
					$html->input($page->ID.'-class','text',$meta['jmpress-class'][0]),
					array('class'=>'cell'));
				foreach ($jmKeys as $key) {
					$divHtml .= $html->div(
						$html->gen('label',$key).
						$html->input($page->ID.'-'.$key,'text',$meta['jmpress-'.$key][0]),
						array('class'=>'cell'));
				}
				foreach ($jmStyleKeys as $key) {
					$divHtml .= $html->div(
						$html->gen('label',$key).
						$html->input($page->ID.'-'.$key,'text',$meta['jmpress-'.$key][0]),
						array('class'=>'cell'));
				}
				$liHtml .= $html->div($divHtml,array('class'=>'form-row'));
				$ulHtml .= $html->li($liHtml,array('class'=>'page','id'=>'page-'.$page->ID));
			}
			echo $html->div($html->div($preview,array('id'=>'preview')),array('id'=>'preview-container'));
			echo $html->div(
				$html->button('preview',array('class'=>'button','onclick'=>'setPreview();'))
				.' '
				.$html->button('zoom in',array('class'=>'button','onclick'=>'zoomInPreview();'))
				.' '
				.$html->button('zoom out',array('class'=>'button','onclick'=>'zoomOutPreview();'))
				,array('class'=>'preview-controls'));
			$ulHtml .= $html->li(
				$html->h(3,'').
				$html->input('action','hidden','save').
				$html->button('auto place',array('class'=>'button','type'=>'button','onclick'=>'autoPlace();')).' '.
				$html->button('save',array('class'=>'button')));
			echo $html->form($html->gen('ul',$ulHtml,array('class'=>'page-list')),'','post');

			echo $html->gen('script','
				$ = jQuery;
				var previewScale = 1;
				var zoomInPreview = function(){
					previewScale += 0.1;
					$("#preview").css("transform","scale("+previewScale+")");
				};
				var zoomOutPreview = function(){
					previewScale -= 0.1;
					$("#preview").css("transform","scale("+previewScale+")");
				};
				var autoPlace = function(){
					var lastx = 0;
					$("li.page").each(function(){
						var id = $(this).attr("id").replace("page-","");
						
						var datax = $(\'input[name="\'+id+\'-data-x"]\',$(this));
						var datay = $(\'input[name="\'+id+\'-data-y"]\',$(this));
						var dataz = $(\'input[name="\'+id+\'-data-z"]\',$(this));
						
						datax.val(lastx+"%");
						datay.val(-lastx+"%");
						dataz.val(-lastx+"%");
						lastx += 100;
					});
				};
				var percent2Px = function(val){
					if(val.indexOf("%")>0){
						val = parseInt(val)/100;
						var w = $("#preview-container").width()/10;
						return Math.floor(w*val);
					}
					return parseInt(val);
				};
				var setPreview = function(){
					$("li.page").each(function(){
						var id = $(this).attr("id").replace("page-","");
						
						var datax = percent2Px($(\'input[name="\'+id+\'-data-x"]\',$(this)).val());
						var datay = percent2Px($(\'input[name="\'+id+\'-data-y"]\',$(this)).val());
						var dataz = percent2Px($(\'input[name="\'+id+\'-data-z"]\',$(this)).val());

						var target = $("#pp-"+id);

						var transform = "translate3d("+datax+"px,"+datay+"px,"+dataz+"px)";

						var datarx = parseInt($(\'input[name="\'+id+\'-data-rotate-x"]\',$(this)).val());
						var datary = parseInt($(\'input[name="\'+id+\'-data-rotate-y"]\',$(this)).val());
						var datarz = parseInt($(\'input[name="\'+id+\'-data-rotate-z"]\',$(this)).val());
						
						isNaN(datarx) ? datarx = 0:void(0);
						isNaN(datary) ? datary = 0:void(0);
						isNaN(datarz) ? datarz = 0:void(0);

 						transform += " rotateX("+datarx+"deg)";
						transform += " rotateY("+datary+"deg)";
						transform += " rotateZ("+datarz+"deg)";
						
						target.css("transform",transform);
					});
				};
				var setActiveStep = function(id){
					var target = $("#preview");
					var src = $("#pp-"+id);
					

					var datax = -percent2Px($(\'input[name="\'+id+\'-data-x"]\').val());
					var datay = -percent2Px($(\'input[name="\'+id+\'-data-y"]\').val());
					var dataz = -percent2Px($(\'input[name="\'+id+\'-data-z"]\').val());


					
					
					var datarx = -parseInt($(\'input[name="\'+id+\'-data-rotate-x"]\').val());
					var datary = -parseInt($(\'input[name="\'+id+\'-data-rotate-y"]\').val());
					var datarz = -parseInt($(\'input[name="\'+id+\'-data-rotate-z"]\').val());
					var transform = "translate3d("+datax+"px,"+datay+"px,"+dataz+"px)";

					isNaN(datarx) ? datarx = 0:void(0);
					isNaN(datary) ? datary = 0:void(0);
					isNaN(datarz) ? datarz = 0:void(0);
					transform += " rotateX("+datarx+"deg)";
					transform += " rotateY("+datary+"deg)";
					transform += " rotateZ("+datarz+"deg)";

					transform += " scale("+previewScale+")";
					console.log(transform);
					target.css("transform",transform);
				};
				$(document).ready(function(){
					$("li.page input").change(setPreview);
					$(".preview-page").click(function(){
						$(".page .form-row").hide();
						var id = $(this).attr("id").replace("pp-","");
						$("#page-"+id+" .form-row").show();
					});
					$("li.page h3").click(function(){
						$(".page .form-row").hide();
						$(".form-row",$(this).parent()).show();
					});
					setPreview();
				});
				');
		}else{
			$args = array(
				'posts_per_page'   => -1,
				//'offset'           => 0,
				'orderby'          => 'menu_order',
				'order'            => 'ASC',
				'post_type'		   => 'page',
				'post_parent'      => 0,
				'post_status'      => 'publish');
			$pages = get_posts($args);
			$tableData = array();

			foreach($pages as $page){
				array_push($tableData, array(
					'ID'=>$page->ID,
					'Title'=>$page->post_title,
					'Edit'=>$html->a('?page=mingusjmpress&id='.$page->ID,'set slides')
					));
			}
			//print_r($pages);
			echo $html->table($tableData,array('class'=>'wp-list-table widefat fixed pages'));
		}
	}
}