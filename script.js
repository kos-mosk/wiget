define(['jquery'], function($){
    var CustomWidget = function () {

    	var self = this;
    	var api;


		this.callbacks = {
			render: function(){
				var templ = '<p id="info">обработка запроса</p>'+
				'<a id="down" download style="display: none;">скачать файл</a>';
				self.render_template(
			      {
		          caption:{
	                  class_name:'download', //имя класса для обертки разметки
	                  },
		          body: templ,//разметка
		          render : '' //шаблон не передается
			       }
			      );
				console.log('render');
				return true;
			},
			init: function(){
				api = self.system();
				return true;
			},
			bind_actions: function(){
				console.log('bind_actions');
				return true;
			},
			settings: function(){
				console.log('settings');
				return true;
			},
			onSave: function(){
				
				return true;
			},
			destroy: function(){
				
			},
		
			contacts: {
					//select contacts in list and clicked on widget name
					selected: function(){

					

					}
				},
			leads: {
				
				selected: function(){
					
				let select = self.list_selected().selected;
		        let id = [];

		        for (let i = 0; i < select.length; i++) {       
		            id[i] = select[i]['id'];          
		        }
				
				$.ajax({
					url: 'https://test/work.php',
					type: 'POST',	
					dataType: 'text',	
					data: {	"id": id,
							"api": api}
					       			
				})
				.success(function(data) {
					$('#info').text("ссылка на скачивание:");
					$('#down').css("display", "block");
					$('#down').attr("href", "https://test/"+data);
					console.log("success");
				})
				.fail(function() {
					console.log("error");
				})
				.always(function() {
					console.log("complete");
				});
				
					
								
					}
				},
			tasks: {
					//select taks in list and clicked on widget name
					selected: function(){
						console.log('tasks');
					}
				}
		};
		return this;
    };

return CustomWidget;
});