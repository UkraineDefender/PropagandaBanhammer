var scrollingContentLength = 0;

setInterval(function() {
    var currentScrollingContentLength = document.querySelector(".scrolling-content").outerHTML.length;

    if(currentScrollingContentLength > scrollingContentLength)
    {
        window.scrollTo(0, document.querySelector(".scrolling-content").scrollHeight);
        scrollingContentLength = currentScrollingContentLength;
    }

    ///////////////////////////////////////////////////////

    var outputStringsDots = document.querySelectorAll(".scrolling-content p .dots");

    for(var i = 0; i < outputStringsDots.length - 1; i++)
    {
        outputStringsDots[i].classList.remove('waiting');
    }

    var lastOutputStringDots = outputStringsDots[outputStringsDots.length - 1];
    lastOutputStringDots.classList.add('waiting');

    ///////////////////////////////////////////////////////

    var outputStringsWaits = document.querySelectorAll(".scrolling-content p .wait-time");
    var lastOutputStringWait = outputStringsWaits[outputStringsWaits.length - 1];

    var lastOutputStringWaitValue = parseInt(lastOutputStringWait.innerHTML) ?? 0;
    var lastOutputStringWaitEndTime = parseInt(lastOutputStringWait.getAttribute('data-end')) ?? 0;

    if(lastOutputStringWaitValue > 0 && lastOutputStringWaitEndTime > 0)
    {
        var timeDiff = lastOutputStringWaitEndTime - (Math.floor(new Date().getTime() / 1000));
        timeDiff = timeDiff < 0 ? timeDiff = 0 : timeDiff;

        lastOutputStringWait.innerHTML = timeDiff;
    }
}, 100);