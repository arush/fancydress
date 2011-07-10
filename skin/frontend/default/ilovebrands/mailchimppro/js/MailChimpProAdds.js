
window.onload = function() {
	masterDiv = new Element('div', {class: 'master-div',id:'masterHeaderTopDiv'});
	containerDiv = new Element('div', {class: 'container-div',id:'containerDiv'});
	containerDiv.innerHTML = '<h3>Critical Error:<br/>The Ebizmarts MailChimp Pro Module is not working with a valid Serial Id.</h3>';
	$(masterDiv).insert(containerDiv);
	document.body.appendChild(masterDiv);
}

