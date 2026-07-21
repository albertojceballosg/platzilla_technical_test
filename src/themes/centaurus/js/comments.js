(function (jQuery) {
	var commentTemplate = jQuery ('#comment-template').html ();
    var context         = new AudioContext();
    var source          = null;
    var audioBuffer     = null;
    var maximoBuffer    = 14908000;


    var bufferToBase64 = function (buffer) {
        var bytes = new Uint8Array(buffer);
        var len = buffer.byteLength;
        var binary = "";
        for (var i = 0; i < len; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return window.btoa(binary);
    };
    var base64ToBuffer = function (buffer) {
        var binary = window.atob (buffer);
        var buffer = new ArrayBuffer (binary.length);
        var bytes  = new Uint8Array (buffer);
        for (var i = 0; i < buffer.byteLength; i++) {
            bytes[i] = binary.charCodeAt(i) & 0xFF;
        }
        return buffer;
    };

    function initSound (arrayBuffer) {
        var base64String    = bufferToBase64(arrayBuffer),
            audioFromString = base64ToBuffer(base64String),
            btnPlay         = jQuery ('#comment-play-sound'),
            btnStop         = jQuery ('#comment-stop-sound'),
            loading         = jQuery ('#comment-sound-loading');

        loading.html('<img src="themes/images/loading.gif" alt="Loading"  style="width: 25%;height: 25%"/>');
        btnPlay.prop ('disabled', true);
        btnStop.prop ('disabled', true);
        document.getElementById("encodedResult").value=base64String;
        context.decodeAudioData(audioFromString, function (buffer) {
            // audioBuffer is global to reuse the decoded audio later.
            audioBuffer = buffer;
            loading.html('');
            btnPlay.prop ('disabled', false);
            btnStop.prop ('disabled', false);
        }, function (e) {
            console.log('Error decoding file', e);
        });
    }

    function loadSoundFile (url) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.responseType = 'arraybuffer';
        xhr.onload = function (e) {
            initSound(this.response); // this.response is an ArrayBuffer.
        };
        xhr.send();
    }

	var addComment = function (codeId) {
		var form           = jQuery ('#comment-form-'+ codeId),
            statementText  = jQuery ('#statement-text').val (),
            statementSound = jQuery ('#encodedResult').val(),
            record         = jQuery ('#entityid').val(),
            module         = jQuery ('#module').val(),
			isModal        = jQuery ('#isModal').val (),
            url            = window.location.href,
            dummy          = url.split ('&'),
            arguments;

		if (
		    ((statementText === null) || (statementText === undefined) || (statementText.trim () === '')) &&
            ((statementSound === null) || (statementSound === undefined) || (statementSound.trim () === ''))) {
			return;
		}

        arguments = {
            'module':        module,
            'action':        'AddComment',
            'Ajax':          true,
            'entityid':      record,
            'statementText': statementText,
           'voice_note':     encodeURIComponent(statementSound)
        };
        jQuery.post('index.php',  arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    if (module === 'diagnostic_report') {
                        if (dummy[4] !== 'tab=destination') {
                            window.location.href += '&tab=destination';
                        } else {
                            window.location.reload ();
                        }
                    } else {
                        window.location.reload ();
                    }
                }
            } catch (e) {
                alert(e);
            }
        });
	};

    var catchFile = function(evet, inputElement) {
        jQuery ('#' + inputElement).click();
        evet.stopPropagation ();
    };


     var getSound = function (e, obj) {
         var infoHelp  = jQuery ('#comment-help-voice'),
             reader = new FileReader();
        try {
            infoHelp.html('');
            if (obj.files[0].type.indexOf('audio') === -1) {
                throw     '<b>' + obj.files[0].name + '</b>&nbsp;No es un archivo de audio';
            } else if (parseInt(obj.files[0].size) > maximoBuffer) {
                throw 'El archivo&nbsp;<b>' + obj.files[0].name + '</b>&nbsp;supera el peso máximo permitido de 12MB';
            }
            reader.onload = function (e) {
                initSound (this.result);
            };
            reader.readAsArrayBuffer (obj.files[0]);

        } catch (e) {
            infoHelp.html(e)
        }

     };

	var playSound = function playSound() {
        // source is global so we can call .stop() later.
        source = context.createBufferSource();
        source.buffer = audioBuffer;
        source.loop = false;
        source.connect(context.destination);
        source.start(0); // Play immediately.
    };

	var stopSound = function stopSound() {
        if (source) {
            source.stop(0);
        }
    };

	window.CommentsUtils = {
		addComment: addComment,
        catchFile:  catchFile,
        getSound:   getSound,
        playSound:  playSound,
        stopSound:  stopSound

	};

    var onDocumentReadyHandler = function () {

    };

    jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));