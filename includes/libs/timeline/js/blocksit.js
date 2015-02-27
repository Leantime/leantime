(function($) {

	//BlocksIt default options
	var blocksOptions = {
		numOfCol: 5,
		offsetX: 5,
		offsetY: 5,
		blockElement: 'div'
	};
	
	//dynamic variable
	var container, colwidth;
	var blockarr = [];
	
	//ie indexOf fix
	if (!Array.prototype.indexOf) {
		Array.prototype.indexOf = function(elt /*, from*/) {
			var len = this.length >>> 0;

			var from = Number(arguments[1]) || 0;
			from = (from < 0) ? Math.ceil(from) : Math.floor(from);
			if (from < 0)
				from += len;

				for (; from < len; from++) {
					if (from in this &&
					this[from] === elt)
					return from;
				}
			return -1;
		};
	}
	
	//create empty blockarr
	var createEmptyBlockarr = function() {
		//empty blockarr
		blockarr = [];
		for(var i=0; i<blocksOptions.numOfCol; i++) {
			blockarrPush('empty-'+i, i, 0, 1, -blocksOptions.offsetY);
		}
	}
	
	//add new block to blockarr
	var blockarrPush = function(id, x, y, width, height) {
		//define block object based on block width
		for(var i=0; i<width; i++) {
			var block = new Object();
			block.x = x + i;
			block.size = width;
			block.endY = y + height + blocksOptions.offsetY*2;
			
			blockarr.push(block);
		}
	}
	
	//remove block from blockarr
	var blockarrRemove = function(x, num) {
		for(var i=0; i<num; i++) {
			//remove block beside
			var index = getBlockIndex(x+i, 'x');
			blockarr.splice(index, 1);
		}
	}
	
	//retrieve block index based on block's x position
	var getBlockIndex = function(value, type) {
		
		for(var i=0; i<blockarr.length; i++) {
			var obj = blockarr[i];
			if(type == "x" && obj.x == value) {
				return i;
			} else if(type == "endY" && obj.endY == value) {
				return i;
			}
		}
	}

	//get height from blockarr range based on block.x and size
	//retrun min and max height
	var getHeightArr = function(x, size) {
		var temparr = [];
		for(var i=0; i<size; i++) {
			temparr.push(blockarr[getBlockIndex(x+i, 'x')].endY);
		}	
		var min = Math.min.apply(Math, temparr);
		var max = Math.max.apply(Math, temparr);
		
		return [min, max, temparr.indexOf(min)];
	}
	
	//get block x and y position
	var getBlockPostion = function(size) {
		
		//if block width is not default 1
		//extra algorithm check
		if(size > 1) {
			//prevent extra loop
			var arrlimit = blockarr.length - size;
			//define temp variable
			var defined = false;
			var tempHeight, tempIndex;
			
			for(var i=0; i<blockarr.length; i++) {
				var obj = blockarr[i];
				var x = obj.x;

				//check for block within range only
				if(x >= 0 && x <= arrlimit) {
					var heightarr = getHeightArr(x, size);
					
					//get shortest group blocks
					if(!defined) {
						defined = true;
						tempHeight = heightarr;
						tempIndex = x;
					} else {
						if(heightarr[1] < tempHeight[1]) {
							tempHeight = heightarr;
							tempIndex = x;
						}
					}
				}
			}
			return [tempIndex, tempHeight[1]];
		} else { //simple check for block with width 1
			tempHeight = getHeightArr(0, blockarr.length);
			return [tempHeight[2], tempHeight[0]];
		}	
	}
	
	//set block position
	var setPosition = function(obj, index) {
		//check block size
		if(!obj.data('size') || obj.data('size') < 0) {
			obj.data('size', 1);
		} else if(obj.data('size') > blocksOptions.numOfCol) {
			obj.data('size', blocksOptions.numOfCol);
		}
		
		//define block data
		var pos = getBlockPostion(obj.data('size'));
		var blockWidth = colwidth * obj.data('size') - (obj.outerWidth() - obj.width());

		//update style first before get object height
		obj.css({
			'width': blockWidth - blocksOptions.offsetX*2,
			'left': pos[0] * colwidth,
			'top': pos[1],
			'position': 'absolute'
		});
		
		var blockHeight = obj.outerHeight();
		
		//modify blockarr for new block
		blockarrRemove(pos[0], obj.data('size'));
		blockarrPush(obj.attr('id'), pos[0], pos[1], obj.data('size'), blockHeight);	
	}
	
	$.fn.BlocksIt = function(options) {
		//BlocksIt options
		if (options && typeof options === 'object') {
			$.extend(blocksOptions, options);
		}
		
		container = $(this);
		colwidth = container.width() / blocksOptions.numOfCol;

		//create empty blockarr
		createEmptyBlockarr();

		container.children(blocksOptions.blockElement).each(function(e) {
			setPosition($(this), e);
		});
		
		//set final height of container
		var heightarr = getHeightArr(0, blockarr.length);
		container.height(heightarr[1] + blocksOptions.offsetY);
		
		return this;
	}

})(jQuery);