    function showDocs(oLink) {
        var oBlock = oLink.getElementsByTagName('div')[0];
        var oIframe = oLink.getElementsByTagName('iframe')[0];
        var oIframeUrl = oLink.getAttribute('url-show');
        if(oBlock.style.height == 0+'px') {
            oBlock.style.height = 858+'px';
            oBlock.style.display = 'block';
            if(oIframe.src != oIframeUrl) {
                oIframe.src = oIframeUrl
            };
        } else {
            oBlock.style.height = 0+'px';
            oBlock.style.display = 'none';
        }
    }