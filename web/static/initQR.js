"use strict";

window.addEventListener("DOMContentLoaded", _ => {
	const params = new URLSearchParams(window.location.search);
	if(!params.get('code')) return;
	const qrLink = "https://qr.bestcj.ro?" + params.get('code');

	const qr = qrcode(0, 'Q');
	qr.addData(qrLink);
	qr.make();

	const svg = qr.createSvgTag(8, 16, "Generated QR code for " + qrLink);

	document.getElementById("generated-qr").innerHTML = svg;
	document.getElementById("generated-qr-download").href = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);
});
