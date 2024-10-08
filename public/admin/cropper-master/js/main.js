
$(function () {

  'use strict';
  var console = window.console || { log: function () {} };
  var $image = $('#image');
  var $download = $('#download');
  var $dataX = $('#dataX');
  var $dataY = $('#dataY');
  var $dataHeight = $('#dataHeight');
  var $dataWidth = $('#dataWidth');
  var $dataRotate = $('#dataRotate');
  var $dataScaleX = $('#dataScaleX');
  var $dataScaleY = $('#dataScaleY');
  var minCroppedWidth = 275;
  var minCroppedHeight = 345;
  var options = {
        autoCrop: true,
		autoCropArea: 1,
		aspectRatio: 500 / 660,
		minCropBoxWidth: 275,
		minCropBoxHeight: 345,
		viewMode: 2,
        preview: '.img-preview',
        crop: function (e) {
          $dataX.val(Math.round(e.x));
          $dataY.val(Math.round(e.y));
          $dataHeight.val(Math.round(e.height));
          $dataWidth.val(Math.round(e.width));
          $dataRotate.val(e.rotate);
          $dataScaleX.val(e.scaleX);
          $dataScaleY.val(e.scaleY);
        }
      };
var uploadedImageType = 'image/jpeg';

  // Tooltip
  $('[data-toggle="tooltip"]').tooltip();


  // Cropper
  $image.on({
    'build.cropper': function (e) {
      //console.log(e.type);
    },
    'built.cropper': function (e) {
      //console.log(e.type);
    },
    'cropstart.cropper': function (e) {
      //console.log(e.type, e.action);
    },
    'cropmove.cropper': function (e) {
      //console.log(e.type, e.action);
    },
    'cropend.cropper': function (e) {
      //console.log(e.type, e.action);
    },
    'crop.cropper': function (e) {
		var widthTexbox = parseInt($("#dataWidth").val());
		var heightTexbox = parseInt($("#dataHeight").val());
		if(e.height < minCroppedHeight || e.width < minCroppedWidth){
			
			//console.log(options);
			
		 
		}

      
      //console.log(e.type, e.x, e.y, e.width, e.height, e.rotate, e.scaleX, e.scaleY);
    },
    'zoom.cropper': function (e) {
      //console.log(e.type, e.ratio);
    }
  }).cropper(options);


  // Buttons
  if (!$.isFunction(document.createElement('canvas').getContext)) {
    $('button[data-method="getCroppedCanvas"]').prop('disabled', true);
  }

  if (typeof document.createElement('cropper').style.transition === 'undefined') {
    $('button[data-method="rotate"]').prop('disabled', true);
    $('button[data-method="scale"]').prop('disabled', true);
  }


  


  // Options
  $('.docs-toggles').on('change', 'input', function () {
    var $this = $(this);
    var name = $this.attr('name');
    var type = $this.prop('type');
    var cropBoxData;
    var canvasData;

    if (!$image.data('cropper')) {
      return;
    }

    if (type === 'checkbox') {
      options[name] = $this.prop('checked');
      cropBoxData = $image.cropper('getCropBoxData');
      canvasData = $image.cropper('getCanvasData');

      options.built = function () {
        $image.cropper('setCropBoxData', cropBoxData);
        $image.cropper('setCanvasData', canvasData);
      };
    } else if (type === 'radio') {
      options[name] = $this.val();
    }

    $image.cropper('destroy').cropper(options);
  });


  // Methods
  $('.docs-buttons').on('click', '[data-method]', function () {
    var $this = $(this);
    var data = $this.data();
    var $target;
    var result;

    if ($this.prop('disabled') || $this.hasClass('disabled')) {
      return;
    }

    if ($image.data('cropper') && data.method) {
      data = $.extend({}, data); // Clone a new one

      if (typeof data.target !== 'undefined') {
        $target = $(data.target);

        if (typeof data.option === 'undefined') {
          try {
            data.option = JSON.parse($target.val());
          } catch (e) {
            console.log(e.message);
          }
        }
      }

      if (data.method === 'rotate') {
        $image.cropper('clear');
      }

      result = $image.cropper(data.method, data.option, data.secondOption);

      if (data.method === 'rotate') {
        $image.cropper('crop');
      }

      switch (data.method) {
        case 'scaleX':
        case 'scaleY':
          $(this).data('option', -data.option);
          break;

        case 'getCroppedCanvas':
          if (result) {
				getImageFile(result.toDataURL('image/jpeg'));
				// Bootstrap's Modal
				/*$('#getCroppedCanvasModal').modal().find('.modal-body').html(result);

				if (!$download.hasClass('disabled')) {
				  $download.attr('href', result.toDataURL('image/jpeg'));
				}
				*/
          }

          break;
      }

      if ($.isPlainObject(result) && $target) {
        try {
          $target.val(JSON.stringify(result));
        } catch (e) {
          console.log(e.message);
        }
      }

    }
  });


  // Keyboard
  $(document.body).on('keydown', function (e) {

    if (!$image.data('cropper') || this.scrollTop > 300) {
      return;
    }

    switch (e.which) {
      case 37:
        e.preventDefault();
        $image.cropper('move', -1, 0);
        break;

      case 38:
        e.preventDefault();
        $image.cropper('move', 0, -1);
        break;

      case 39:
        e.preventDefault();
        $image.cropper('move', 1, 0);
        break;

      case 40:
        e.preventDefault();
        $image.cropper('move', 0, 1);
        break;
    }

  });


  // Import image
  var $inputImage = $('#inputImage');
  var URL = window.URL || window.webkitURL;
  var blobURL;

  if (URL) {
    $inputImage.change(function () {
      var files = this.files;
      var file;

      if (!$image.data('cropper')) {
        return;
      }
      if (files && files.length) {
        file = files[0];

        if (/^image\/\w+$/.test(file.type)) {
          blobURL = URL.createObjectURL(file);
          $image.one('built.cropper', function () {

            // Revoke when load complete
            URL.revokeObjectURL(blobURL);
          }).cropper('reset').cropper('replace', blobURL);
          $inputImage.val('');
        } else {
          window.alert('Please choose an image file.');
        }
      }
	  

    });
  } else {
    $inputImage.prop('disabled', true).parent().addClass('disabled');
  }

});

var globalRation = 1;
function testsss(typeFrom=1){
	var $image = $('#image');
	var currentWith = parseInt($('#dataWidth').val());
	var currentHeight = parseInt($('#dataHeight').val());
	if(typeFrom==2){
		currentWith = 275;
	}   currentHeight = 345;


	var pic_real_width = 0;
	var pic_real_height = 0;
	$("<img/>").attr("src", $image.attr("src")).load(function() {
		getRatioRealSize(this.width,currentWith);
    });
	setTimeout(function() {
		var ratio = globalRation;
		//var ratio = $image.cropper.imageData.width /  $image.cropper.imageData.naturalWidth; 
		$image.cropper('setCropBoxData', {width:currentWith*ratio,height:currentHeight*ratio});
	}, 1000);
	
	
}

function getRatioRealSize(naturalWidth,currentWith){
	
	var ratio  = parseFloat(currentWith / naturalWidth).toFixed(2);
	globalRation = ratio;
	return ratio;
}

