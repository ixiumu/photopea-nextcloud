var Photopea = {
	_currentContext: null,
	_file: {},
	_fileList: null,
	_lastTitle: '',
	_extensions: [],
	_editor: OC.generateUrl('/apps/photopea/sources/index.html'),
	registerExtension: function(objs) {
		var self = this;
		if (!Array.isArray(objs)) {
			objs = [objs];
		}
		objs.forEach(function(obj){
			self._extensions.push(obj);
		});
	},

	registerFileActions: function() {
		var mimes = this.getSupportedMimetypes(),
			_self = this;
        
		$.each(mimes, function(key, value) {
			OCA.Files.fileActions.registerAction({
				name: 'Edit',
				mime: value,
				actionHandler: _.bind(_self._onEditorTrigger, _self),
				permissions: OC.PERMISSION_READ,
				icon: function () {
					return OC.imagePath('core', 'actions/edit');
				}
			});
			OCA.Files.fileActions.setDefault(value, 'Edit');
		});
	},
	getSupportedMimetypes: function() {
		var result = [];
		this._extensions.forEach(function(obj){
			result = result.concat(obj.mimes);
		});
		return result;
	},
	show: function() {
		var self = this;
		var viewer = OC.generateUrl('/apps/photopea/sources/index.html');
		var api = OC.generateUrl('/apps/photopea/api');
		
		// static html
		//var url = viewer+'#'+encodeURI('{"files":["'+api+this._file.fullName+'"],"resources":[],"server":{"version":1,"url":"'+api+'/api","formats":["'+this._file.fullName.split('.').pop().toUpperCase()+'"]}}');
		//window.open(url);

		// app
		var url = OC.generateUrl('/apps/photopea/')+'?name='+this._file.name+'&dir='+this._file.dir;
		window.location.href = url;

		// pop
		// var $iframe = $('<iframe id="peaframe" style="width:100%;height:100%;display:block;position:absolute;top:0;z-index:1999;padding-top:inherit;" src=\''+url+'\' sandbox="allow-scripts allow-same-origin allow-downloads allow-popups allow-modals allow-top-navigation allow-presentation" allowfullscreen="true"/>');

		// if ($('#isPublic').val()) {
		// 	// force the preview to adjust its height
		// 	$('#preview').append($iframe).css({height: '100%'});
		// 	$('body').css({height: '100%'});
		// 	$('#content').addClass('full-height');
		// 	$('footer').addClass('hidden');
		// 	$('#imgframe').addClass('hidden');
		// 	$('.directLink').addClass('hidden');
		// 	$('.directDownload').addClass('hidden');
		// 	$('#controls').addClass('hidden');
		// } else {
		// 	$('#app-content').after($iframe);
		// }

		// $("#pageWidthOption").attr("selected","selected");
		// // replace the controls with our own
		// $('#app-content #controls').addClass('hidden');

		// $('#peaframe').load(function(){
		// 	var iframe = $('#peaframe').contents();

		// 	OC.Apps.hideAppSidebar();

		// 	self._lastTitle = document.title;

		// 	var filename = self._file.name ? self._file.name : $('#filename').val();
		// 	document.title = filename + ' - ' + OC.theme.title;

		// 	// iframe.find('#close-button').click(function() {
		// 	// 	self.hide();
		// 	// });

    	// 	if(!$('html').hasClass('ie8')) {
    	// 		history.pushState({}, '', '#photopea');
    	// 		$(window).one('popstate', function () {
    	// 			self.hide();
    	// 		});
    	// 	}
		// });

	},
	// hide: function() {
	// 	$('#peaframe').remove();
	// 	if ($('#isPublic').val() && $('#filesApp').val()){
	// 		$('#controls').removeClass('hidden');
	// 		$('#content').removeClass('full-height');
	// 		$('footer').removeClass('hidden');
	// 	}

	// 	if (!$('#mimetype').val()) {
	// 		FileList.setViewerMode(false);
	// 	}

	// 	// replace the controls with our own
	// 	$('#app-content #controls').removeClass('hidden');

	// 	document.title = this._lastTitle;

	// 	if (!$('#mimetype').val()) {
	// 		this._fileList.addAndFetchFileInfo(this._file.dir + '/' + this._file.name, '');
	// 	} else {
	// 		//TODO
	// 	}
	// },
	_onEditorTrigger: function(fileName, context) {
		this._currentContext = context;
		this._file.name = fileName;
		this._file.dir = context.dir;
		this._fileList = context.fileList;
		var fullName = context.dir + '/' + fileName;
		if (context.dir === '/') {
			fullName = '/' + fileName;
		}
		this._file.fullName = fullName;
        
		this.show();
	},
}

Photopea.Extensions = {};

Photopea.Extensions.PSD = {
	name: 'psd',
	mimes: ["application/x-photoshop","application/illustrator","application/x-gimp","application/coreldraw","application/postscript"],
	encode: function(data) {
        return new Promise(function(resolve, reject) {
			resolve(data);
        });
	},
	decode: function(data) {
		return new Promise(function(resolve, reject) {
			try {
				resolve(JSON.parse(data));
			} catch (e) {
				resolve(data);
			}
        });
	}
};

Photopea.NewFileMenuPlugin = {

	attach: function(menu) {
		// only attach to main file list, public view is not supported yet
		if (menu.fileList.id !== 'files') {
			return;
		}

		// register the new menu entry
		menu.addMenuEntry({
			id: 'photopeafile',
			displayName: t('photopea', 'New PSD file'),
			templateName: t('photopea', 'New File.psd'),
            iconClass: 'icon-picture',
			fileType: 'x-photoshop',
			actionHandler: function(name) {
				var dir = menu.fileList.getCurrentDirectory();
				// menu.fileList.createFile(name).then(function() {
				// 	//
				// });
				
				$.post(OC.generateUrl('apps/photopea/api/create'),
				{
					name: name,
					dir: dir
				},
				function onSuccess(response) {
					if (response.error) {
						OCP.Toast.error(response.error);
						return;
					}

					menu.fileList.add(response, { animate: true });
					OCP.Toast.success(t('photopea', 'File created'));
				});
			}
		});
	}
};


OC.Plugins.register('OCA.Files.NewFileMenu', Photopea.NewFileMenuPlugin);

$(document).ready(function(){
	Photopea.registerExtension([Photopea.Extensions.PSD]);
	Photopea.registerFileActions();
});