/*	
     #############################################
	
                         |
                       {   }
                     <   -   >                ( Usa el código como quieras, pero NO este logotipo ) ¬¬	
                       {   }
                         |
								
               CropJC plugin for jQuery.
           Developer by Juan Andŕes Carmena
						  
     #############################################
	
Version: 1.1.0
Author: Juan Andrés Carmena <juan14nob@gmail.com>
Website: https://github.com/jcdesignweb/CropJC
Docs: https://github.com/jcdesignweb/CropJC
Repo: https://github.com/jcdesignweb/CropJC
Issues: https://github.com/jcdesignweb/CropJC/issues

*/
(function($){
	var settings;
	var ej;
	
	var _Image;
	var CropData;
	
	var warnings = {
		'squaresize': 'Missing square size',
		'restricts': 'Missing '
	}
	var error;
	
	var defaults = {
		DetailInformation : "#Detail",
		IFile : "#inpFile", // input file selector
		ImgCont : "#Img",
		ImgCropped: "", // div id to show image cropped
		minResize : 50,
		display_crop: false,
		square: false
	}
	
	var coordinatesImage = function() {
		var offset = $(settings.ImgCont).offset();
		
		this.left = offset.left;
		this.top = offset.top;
            
		this.getTop=function() {
			return this.top;
		};
			
		this.getLeft=function() {
			return this.left;
		};
	}
	
	/**
	 * w = Width
	 * h = Height
	 * t = Top
	 * l = Left
	 */
	var coordinatesCropImage = function(w,h,t,l) {
		
		this.width = w;
		this.height = h;
		
		this.left = l;
		this.top = t;
            
		this.getTop=function() {
			return this.top;
		};
			
		this.getLeft=function() {
			return this.left;
		};
		
		this.getWidth=function() {
			return this.width;
		};
		
		this.getHeight=function() {
			return this.height;
		};
	};
	
	var NewImage = function( width, height, size, name ) {
		
		this.Width = width;
		this.Height = height;
		
		this.Size = size;
		this.Name = name;
			
		this.getWidth = function() {
			return this.Width;
		};
		
		this.getHeight = function() {
			return this.Height;
		};
		
		this.getSize = function() {
			return this.Size + " Kb";
		};
		
		this.getName = function() {
			return this.Name;
		};
	};
	
	var getCuttingCoordinates = function(el, imageLocated) {
		var offset = $(el).offset();
		
		var x,y; // px
		
		x= ( offset.left - imageLocated.getLeft() );
		y= ( offset.top - imageLocated.getTop() );
		
		var _x,_y,_w,_h; // %
		
		_x= ( (x *100) / parseFloat(_Image.getWidth())).toFixed(2); 
		_y= ((y*100) / parseFloat(_Image.getHeight())).toFixed(2);
		
		_w= (($(el).width()*100) /_Image.getWidth()); 
		_h= (($(el).height()*100) /_Image.getHeight());
		
		CropData=new coordinatesCropImage( _w, _h, _y, _x);
		
	};	
	
	var methods = {
			
		addAreaCutting: function() {
			if(_Image != undefined) { // Si la imagen fue seteada entonces corto la imagen
				
				var top,left;
				
				var cropw = (_Image.getWidth() / 2);
				var croph = (_Image.getHeight() / 2);
				
				$(".xpos").html(cropw);
				$(".ypos").html(croph);
				
				var imageLocated = new coordinatesImage();
				
				$("#Img")
					.append('<div class="JcCutting"></div>')
					.height(_Image.getHeight())
					.width(_Image.getWidth());
				
				var Cutt = $("#Img").find(".JcCutting");
				
				Cutt.append('<div class="ui-resizable-handle ui-resizable-nw" id="nwgrip"></div>');
				Cutt.append('<div class="ui-resizable-handle ui-resizable-ne" id="negrip"></div>');
				Cutt.append('<div class="ui-resizable-handle ui-resizable-sw" id="swgrip"></div>');
				Cutt.append('<div class="ui-resizable-handle ui-resizable-se" id="segrip"></div>');
				
				if(settings.square === true) {
					cropw=settings.size;
					croph=settings.size;
					
					top = (_Image.getHeight()-settings.size)  / 2;
					left = (_Image.getWidth()-settings.size) / 2;
				}else{
					
					top = (_Image.getHeight()-croph)  / 2;
					left = (_Image.getWidth()-cropw) / 2;
				}
				
				Cutt.width(cropw)
					.height(croph)
					.css({ top: top, left: left })
					
					.resizable({ 
						minHeight: settings.minResize,
						minWidth: settings.minResize,
						containment: "#prevImage", 
						aspectRatio: true,
						handles: {
		                    'ne': '#negrip',
		                    'se': '#segrip',
		                    'sw': '#swgrip',
		                    'nw': '#nwgrip'
		                },
						resize: function() {
							getCuttingCoordinates(this,imageLocated);
						}
					})
					
					.draggable(
						{
							containment: "#prevImage",
							drag: function() {
					            
								getCuttingCoordinates(this,imageLocated);
					        }
						}
					);
				
				
				methods.setCss();
				
				getCuttingCoordinates($( ".JcCutting" ), imageLocated);
				
			}else{
				alert("Falta imagen");
			}
		},
			
		CreateDetail: function(width, height, ImageSize, ImageName) {
			
			var Size = (parseInt(ImageSize) / 1000);
			_Image = new NewImage(width, height, Size, ImageName);
		},
		
		Crop: function() {
			var Form = "#formFile";
			
			var square = false;
			if(settings.square) 
				square = settings.size;
			
				
			var formData = new FormData(this);
			var dimens = {
				cropW: CropData.getWidth(),
				cropH: CropData.getHeight(),
				xPos: CropData.getLeft(),
				yPos: CropData.getTop(),
				square: square
			}
			
			formData.append("dimensions", JSON.stringify(dimens));
			
			var jqxhr = $.ajax( 
			{
				processData: false,
				contentType: false,
				mimeType:"multipart/form-data",
				cache: false,
				type:"POST",
				data: formData,
				url: settings.url
			})
			.done(function(data) {
				var json = $.parseJSON(data);
				
				if(json.error == false) {
					methods.showCropped(json.cropped);
				}else{
					error = json.error;
					methods.showError();
					//alert("Error");
				}
			})
			.fail(function() {
				console.log("error");
			})
			.always(function() {
			});
				
			return false;
		},
		
		init: function(options, rootElement) {
			
			
			if($.ui === undefined) {
				
				/**
				 * get ui for animations
				 */
				$.getScript( "js/jquery-ui.js", function( data, textStatus, jqxhr ) {
					console.log( textStatus ); // Success
					console.log( jqxhr.status ); // 200
					console.log( "Load was performed." );
				});
			}
			
			
			settings = $.extend({}, defaults, options);
			return methods.validateParams();
		},
		
		setCss: function() {
			
			$(".JcCutting").css({
				'background-color': '#000',
				'border': '1px solid #000000',
				'cursor': 'all-scroll !important',
			    'overflow': 'hidden',
			    'opacity': '.35',
			    'position': 'relative'
			});
			
			$("#prevImage").css({ 'position': 'absolute' });
			
			$("#nwgrip, #negrip, #swgrip, #segrip, #ngrip, #egrip, #sgrip, #wgrip").css({
				'width': '10px',
		    	'height': '10px',
		    	'background-color': '#ffffff',
		    	'border': '1px solid #000000'
			});
			
			$("#segrip").css({ "right": "-5px",	"bottom": "-5px" });
			
		},
		
		/**
		 *  @param IFile = Es el input file que va a tomar la imágen
		 */
		setImage: function(input) {
			
			
			if ( input.files && input.files[0] ) {
				
				var ImageSize, ImageName;
				ImageSize = input.files[0].size;
				ImageName = input.files[0].name;
				
				var previewImagecontent = '<div id="prevImage"><img id="img" width="500" /></div>';
					
				var FR = new FileReader();
				FR.onload = function(e) {
					var image = new Image();
				    image.src = e.target.result;
				    image.onload = function() {
				    	
				    	$(settings.ImgCont).html(previewImagecontent);
				        $('#prevImage img#img').attr( "src", this.src );

				        methods.CreateDetail($("img#img").width(), $("img#img").height(), ImageSize, ImageName);
				        
				        methods.addAreaCutting();
				        
				        if(settings.display_crop === true) {
				        	methods.showInfo();	
				        }
				    };
				};
				
		        FR.readAsDataURL( input.files[0] );
		    }
		},
		
		
		validateParams: function() {
			var stricts = ['url','cropped_path'];
			
			for(strict in stricts) {
				if(settings.hasOwnProperty(stricts[strict]) === false) {
					error = warnings.restricts + "{" + stricts[strict] + "}";
					return false;
				}
			}
			
			if(settings.square) {
				settings.minResize = settings.size;
				
				if(settings.hasOwnProperty('size') === false) {
					error = warnings.squaresize;
					return false;	
				}
			}
			
			return true;
		},
		
		
		showError: function() {
			alert(error);
		},
		
		showInfo: function() {
			
			$(settings.DetailInformation).append("<div class='cropped'></div>").append("</span><br />");
			
		},
		
		showCropped: function(image) {
			$(settings.DetailInformation + " .cropped").html('<img src="'+settings.cropped_path+image+'"/>').append('<br />');
		}
	}
	
	$.fn.extend ({
        
		CropJC: function(options) {
			
			if(methods.init(options, this)) {
			
				this.delegate("#formFile", "submit", methods.Crop);
				
				this.delegate("#BtnCrop", "click", function() {
					$("#formFile").submit();
				});
				
				this.delegate(settings.IFile, "change", function() {
					methods.setImage(this);
				});
			}else{
				methods.showError();
			}
        }
	});

})(jQuery);
