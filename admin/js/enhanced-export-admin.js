const _$ = function (selector) { return document.querySelector(selector) }
const _$$ = function (selector) { return Array.from(document.querySelectorAll(selector)) }

window.addEventListener('load', function () {
	_$$('.ee-input-wrapper span').forEach(cross => {
		cross.addEventListener('click', function(e) {
			e.preventDefault();
			cross.previousElementSibling.value = '';
		})
	})
})

