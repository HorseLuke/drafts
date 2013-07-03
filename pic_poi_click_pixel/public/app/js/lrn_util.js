if(!window.lrn){
	window.lrn = {};
}

if(!window.lrn.util){
	window.lrn.util = {};
}

(function(util, $){
	
	/*
	 * 兼容firefox，获取eventOffset的offsetX和offsetY。
	 * 用法：lrn.util.eventOffset.get(event);
	 * @link http://social.msdn.microsoft.com/Forums/zh-CN/3527adf4-c42a-4b15-977f-1d78fbb54597/eventoffsetx-firefox-
	 */
	util.eventOffset = {
		"get": function(e){
			if(e.offsetX == undefined){
				var evtOffsets = this.getOffset(e);
				return {offsetX: evtOffsets.offsetX, offsetY: evtOffsets.offsetY};
			}else{
				return {offsetX: e.offsetX, offsetY: e.offsetY};
			}
		},
		
		"getOffset": function(evt){
			var target = evt.target;
			if (target.offsetLeft == undefined){
				target = target.parentNode;
			}
			var pageCoord = this.getPageCoord(target);
			var eventCoord = { 
				x: window.pageXOffset + evt.clientX,
				y: window.pageYOffset + evt.clientY
			};
			var offset = {
				offsetX: eventCoord.x - pageCoord.x,
				offsetY: eventCoord.y - pageCoord.y
			};
			return offset;
		},
		
		"getPageCoord": function(element){
			var coord = {x: 0, y: 0};
			while (element){
				coord.x += element.offsetLeft;
				coord.y += element.offsetTop;
				element = element.offsetParent;
			}
			return coord;
		},
		
	};
	
	util.img_ori_size = {
		
		"ori_size_cache": {},
		
		/*
		 * 使用onload获取图像实际宽度和高度，并且进行缓存，防止多次资源消耗
		 * 用法lrn.util.img_ori_size.get(源图片地址,成功函数(成功获取的图像实际宽高),失败函数(e));
		 */
		"get": function(src, success, error){
			if(this.ori_size_cache[src] == undefined){
				var img=new Image();
				var data_cache = util.img_ori_size.ori_size_cache;
				img.onload = function(e){
					data_cache[src] = {width:img.width, height:img.height};
					if(success != undefined){
						success(data_cache[src]);
					}
				};
				img.onerror = function(e){
					this.onerror = null;
					console.log(e);
					if(error != undefined){
						error(e);
					}
				};
				img.src = src;
			}else{
				success(this.ori_size_cache[src]);
			}
			
		}
		
	};
	
	
})(window.lrn.util, $);
