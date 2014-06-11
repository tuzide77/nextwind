/*
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 */
;(function () {
	var lock = false;

	Wind.use('ajaxForm', function(){
		//绑定
		var form = $('#J_majiaband_form'),
			btn = form.find('button:submit');
		form.ajaxForm({
			dataType: 'json',
			beforeSubmit: function(){
				Wind.Util.ajaxBtnDisable(btn);
			},
			success: function(data){
				Wind.Util.ajaxBtnEnable(btn);
				if(data.state == 'success') {
					Wind.Util.formBtnTips({
						wrap: btn.parent(),
						msg: data.message,
						callback: function(){
							location.reload();
						}
					});
				}else{
					Wind.Util.formBtnTips({
						error: true,
						wrap: btn.parent(),
						msg: data.message
					});
				}
			}
		});

		//弹窗操作
		
		$('a.J_majia_handle').on('click', function(e){
			e.preventDefault();

			if(lock) {
				return;
			}
			lock = true;

			checkBind($(this));
		});
	});

	function checkBind(elem){
		Wind.Util.ajaxMaskShow();
		$.get(elem.attr('href'), function(data){
			lock = false;
			Wind.Util.ajaxMaskRemove();
			if(data.state == 'success') {
				Wind.Util.resultTip({
					follow: elem,
					msg : data.message,
					callback: function(){
						location.reload();
					}
				});
			}else{
				if(data.data.reband) {
					rebind(data.data.reband, '重新绑定');
					return;
				}

				Wind.Util.resultTip({
					follow: elem,
					error: true,
					msg : data.message
				})
			}

		}, 'json').fail(function(){
			lock = false;
		});
	}
	
	//重新绑定
	function rebind(url, title){
		Wind.use('dialog', function(){
			Wind.Util.ajaxMaskShow();
			$.get(url, function(data){
				Wind.Util.ajaxMaskRemove();
				if(Wind.Util.ajaxTempError(data)) {
					return;
				}
				Wind.dialog.closeAll();
				Wind.dialog.html(data, {
					title: title,
					isDrag: true,
					isMask: false,
					callback: function(){
						var form = $('#J_reband_form'),
							btn = form.find('button:submit');
						form.ajaxForm({
							dataType: 'json',
							beforeSubmit: function(){
								Wind.Util.ajaxBtnDisable(btn);
							},
							success: function(data){
								Wind.Util.ajaxBtnEnable(btn);
								if(data.state == 'success') {
									Wind.Util.formBtnTips({
										wrap: btn.parent(),
										msg: data.message,
										callback: function(){
											location.reload();
										}
									});
								}else{
									Wind.Util.formBtnTips({
										error: true,
										wrap: btn.parent(),
										msg: data.message
									});
								}
							}
						});

						$('a.J_majia_pop_handle').on('click', function(e){
							e.preventDefault();
							checkBind($(this));
						});
					}
				});

			}, 'html');

		});
	}

	//head马甲切换
	$('#J_head_majia').on('click', function(e){
		e.preventDefault();

		if(lock) {
			return;
		}
		lock = true;
		rebind($(this).data('uri'), '马甲切换');
	});

})();