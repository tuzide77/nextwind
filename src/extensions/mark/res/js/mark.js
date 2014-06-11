 /**
 * @Descript: 评分
 * @Author	: linhao87@gmail.com
 */
;
(function(){
	var m_lock = false;
	var pid = $(this).data('pid'),
		J_read_mark_get = $('#J_read_mark_get_'+ pid);

	$('a.J_plugin_read_mark').on('click', function(e){
		e.preventDefault();
		if(m_lock) {
			return;
		}
		m_lock = true;

		Wind.Util.ajaxMaskShow();
		$.post($(this).data('uri'), function(data){
			m_lock = false;
			Wind.Util.ajaxMaskRemove();
			if(Wind.Util.ajaxTempError(data)) {
				return;
			}

			Wind.use('dialog', function(){
				Wind.dialog.html(data, {
					id: 'J_mark_pop',
					title: '评分',
					isMask: false,
					isDrag: true,
					callback: function(){
						markHandles();
					}
				});
			});
		}, 'html')
		.fail(function(){
			m_lock = false;
		});
		
	});

	//评分弹窗操作
	function markHandles(){
		//点击分数
		$('a.J_mark_item').on('click', function(e){
			e.preventDefault();
			var $this = $(this),
				tr = $this.parents('tr'),
				credits_input = tr.find('input.J_mark_credits');

			credits_input.val($this.text());
		});

		//理由
		$('#J_mark_reason_select').on('change', function(){
			$('#J_mark_reason').val(this.value);
		});

		//提交
		var form = $('#J_mark_form'),
			btn = form.find('button:submit');
		form.on('submit', function(e){
			e.preventDefault();
			Wind.use('ajaxForm', function(){
				form.ajaxSubmit({
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
									$('#J_mark_pop').remove();
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
			});

		});
	}


	var marklist_form = $('form.J_marklist_form');
	if(!marklist_form.length) {
		return;
	}

	//全选
		marklist_form.each(function (i, o) {
			var cur_form = $(this),
				check_all,
				check_items;

			cur_form.on('change', 'input.J_markcheck_all', function (e) {
				check_items = cur_form.find('input.J_markcheck');
				if (this.checked) {
					//全选
					check_items.prop('checked', true);
				} else {
					//取消全选
					check_items.prop('checked', false);
				}
			}).on('change', 'input.J_markcheck', function (e) {
				//点击(非全选)复选框
				check_items = cur_form.find('input.J_markcheck');
				check_all = cur_form.find('input.J_markcheck_all');

				if (this.checked) {
					if (!check_items.filter(':not(:checked)').length) {
						check_all.prop('checked', true); //所有全选打钩
					}
				} else {
					check_all.prop('checked', false); //取消全选
				}
			});
		});

		//删除
		Wind.use('ajaxForm', function(){
			marklist_form.ajaxForm({
				dataType: 'json',
				beforeSubmit: function(arr, $form, options){
					Wind.Util.ajaxBtnDisable($form.find('button.J_marklist_del'));
				},
				success: function(data, statusText, xhr, $form){
					var btn = $form.find('button.J_marklist_del');
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
		});

	//翻页
	var p_lock = false;
	marklist_form.on('click', '.J_plugin_mark_page a', function(e){
		e.preventDefault();
		var $this = $(this);
		if(p_lock) {
			return;
		}
		p_lock = false;

		Wind.Util.ajaxMaskShow();
		$.get(this.href, function(data){
			if(Wind.Util.ajaxTempError(data)) {
				return;
			}
			Wind.Util.ajaxMaskRemove();
			var form = $this.parents('form.J_marklist_form');

			form.html(data);
			var avas = form.find('img.J_avatar');
			if(avas.length) {
				Wind.Util.avatarError(avas);
			}
		});
	});
	
})();