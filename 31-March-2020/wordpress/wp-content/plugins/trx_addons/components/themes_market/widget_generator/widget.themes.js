/**
 * Widget "Themes List"
 *
 * @package WordPress
 * @subpackage ThemeREX Addons
 * @since v1.6.33
 */

(function(){
	"use strict";

	// Create object
	window.TRX_Addons_Widget_Themes = function(params) {
		this.params = params;
		this.show();
	};
	TRX_Addons_Widget_Themes.prototype.getQueryParams = function() {
		var list = {};
		for (var i in this.params) {
			if (i!='downloads' && i!='columns')
				list[i] = this.params[i];
		}
    	return list;
	};
	TRX_Addons_Widget_Themes.prototype.getContainer = function() {
		var node = document.getElementById(this.params.uid);
		if (!node) console.error("Container ID not defined or not exists");
		return node;
	};
	TRX_Addons_Widget_Themes.prototype.getDownloadsUrl = function() {
    	return this.addParamsToUrl(document.location.protocol + this.params.downloads + '/wp-json/trx_addons/v1/themes/list', this.getQueryParams());
	};
	TRX_Addons_Widget_Themes.prototype.addParamsToUrl = function(loc, prm) {
		var ignore_empty = arguments[2] !== undefined ? arguments[2] : true;
		var q = loc.indexOf('?');
		var attr = {};
		if (q > 0) {
			var qq = loc.substr(q+1).split('&');
			var parts = '';
			for (var i=0; i<qq.length; i++) {
				var parts = qq[i].split('=');
				attr[parts[0]] = parts.length>1 ? parts[1] : ''; 
			}
		}
		for (var p in prm) {
			attr[p] = prm[p];
		}
		loc = (q > 0 ? loc.substr(0, q) : loc) + '?';
		var i = 0;
		for (p in attr) {
			if (ignore_empty && attr[p]=='') continue;
			loc += (i++ > 0 ? '&' : '') + p + '=' + attr[p];
		}
		return loc;
	};
	
	// Display themes
	TRX_Addons_Widget_Themes.prototype.show = function() {
		if (typeof XMLHttpRequest == 'undefined') {
			console.error("Unsupported platform: Unable to do remote requests because there is no XMLHTTPRequest implementation in your browser");
			return;
		}
		var widget = this;
		var r = new XMLHttpRequest;
		r.onreadystatechange = function() {
			if (r.readyState == 4) {
				var response = r.status == 200 
									? JSON.parse(r.responseText) 
									: {error: 'Service temporary unavailable!'};
				var node = widget.getContainer();
				if (node) {
					if (response.css && node.getElementsByTagName('link').length==0) {
						var s = document.createElement('link');
						s.async = true;
						s.type = 'text/css';
						s.rel = 'stylesheet';
						s.property = 'stylesheet';
						s.href = response.css+'?ver='+Math.random();
						node.appendChild(s);
					}
					var items = node.getElementsByTagName('div');
					if (items.length==0) {
						var s = document.createElement('div');
						s.className = 'trx_addons_widget_themes';
						node.appendChild(s);
						items = node.getElementsByTagName('div')[0];
					}
					var html = '';
					if (response.error)
						html += '<div class="trx_addons_error">'+response.error+'</div>';
					else if (response.list.length > 0) {
						if (widget.params['columns'] > 1)
							html += '<div class="trx_addons_widget_themes_columns trx_addons_columns_wrap">'
						for (var i=0; i<response.list.length; i++) {
							html += (widget.params['columns'] > 1 
										? '<div class="trx_addons_column-1_'+widget.params['columns']+'">'
										: '')
									+ '<div class="trx_addons_widget_themes_item">'
										+ (response.list[i].featured
											? '<div class="trx_addons_widget_themes_item_featured" style="background-image:url('
												+ response.list[i].featured
												+ ');"></div>'
											: '')
										+ '<div class="trx_addons_widget_themes_item_header">'
											+ '<h4 class="trx_addons_widget_themes_item_title">' 
												+ '<a href="' + response.list[i].download_url + '" target="_blank">' 
													+ response.list[i].title 
												+ '</a>'
											+ '</h4>'
											+ '<div class="trx_addons_widget_themes_item_price">' + response.list[i].price + '</div>'
											+ '<div class="trx_addons_widget_themes_item_meta">' 
												+ '<span class="trx_addons_widget_themes_item_version">'
													+ '<span>v.</span>'
													+ '<span>' + response.list[i].version + '</span>'
												+ '</span>'
												+ '<span class="trx_addons_widget_themes_item_date">'
													+ '<span>' + response.list[i].date_updated + '</span>'
												+ '</span>'
											+ '</div>'
										+ '</div>'
										+ (response.list[i].content
											? '<div class="trx_addons_widget_themes_item_content">' + response.list[i].content + '</div>'
											: '')
									+ '</div>'
									+ (widget.params['columns'] > 1 
										? '</div>'
										: '');
						}
						if (widget.params['columns'] > 1)
							html += '</div>';
					} else
						html += '<div class="trx_addons_error">Themes list is empty</div>';
					items.innerHTML = html;
				}
			}
		};
		r.open("GET", this.getDownloadsUrl(), true);
		r.send();
	};
	
})();