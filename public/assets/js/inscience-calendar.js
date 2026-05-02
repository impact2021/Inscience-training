/* global FullCalendar, inscienceCalendarData */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var calEl = document.getElementById('inscience-fullcalendar');
		if (!calEl) return;

		var calendar = new FullCalendar.Calendar(calEl, {
			initialView: 'dayGridMonth',
			headerToolbar: {
				left:   'prev,next today',
				center: 'title',
				right:  'dayGridMonth,listMonth'
			},
			events: inscienceCalendarData.events || [],
			eventClick: function (info) {
				openModal(info.event);
			},
			eventDidMount: function (info) {
				// Add tooltip title
				info.el.title = info.event.title;
			},
			height: 'auto',
			fixedWeekCount: false,
		});

		calendar.render();

		// --- Modal logic ---
		var overlay = document.getElementById('inscience-modal-overlay');
		var closeBtn = overlay ? overlay.querySelector('.inscience-modal-close') : null;

		if (overlay) {
			overlay.addEventListener('click', function (e) {
				if (e.target === overlay) closeModal();
			});
		}

		if (closeBtn) {
			closeBtn.addEventListener('click', closeModal);
		}

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') closeModal();
		});

		function openModal(event) {
			if (!overlay) return;
			var props = event.extendedProps || {};

			setText('.inscience-modal-title',   event.title);
			setText('.inscience-modal-type',    props.course_type ? capitalise(props.course_type) : '—');
			setText('.inscience-modal-date',    formatDateRange(event.start, event.end));
			setText('.inscience-modal-time',    props.time || '—');
			setText('.inscience-modal-location',props.location || '—');
			setText('.inscience-modal-us',      props.us_codes || '—');
			setText('.inscience-modal-price',   props.price ? 'NZ$' + props.price : '—');
			setText('.inscience-modal-description', props.description || '');

			var statusEl = overlay.querySelector('.inscience-modal-status');
			if (statusEl) {
				var status = props.status || 'open';
				statusEl.textContent = capitalise(status);
				statusEl.className = 'inscience-modal-status inscience-badge inscience-badge-' + status;
			}

			// Toggle rows with empty values
			toggleRow('.inscience-modal-row-time',     !!props.time);
			toggleRow('.inscience-modal-row-us',       !!props.us_codes);
			toggleRow('.inscience-modal-row-price',    !!props.price);

			// Enrol button
			var enrolBtn = overlay.querySelector('.inscience-modal-enrol');
			if (enrolBtn) {
				if (props.enrol_url && props.status !== 'cancelled' && props.status !== 'full') {
					enrolBtn.href = props.enrol_url;
					enrolBtn.style.display = '';
				} else if (props.status === 'full') {
					enrolBtn.textContent = 'Course Full';
					enrolBtn.style.display = '';
					enrolBtn.href = '#';
				} else {
					enrolBtn.style.display = 'none';
				}
			}

			overlay.style.display = 'flex';
			document.body.style.overflow = 'hidden';
		}

		function closeModal() {
			if (overlay) overlay.style.display = 'none';
			document.body.style.overflow = '';
		}

		function setText(selector, text) {
			var el = overlay.querySelector(selector);
			if (el) el.textContent = text || '';
		}

		function toggleRow(selector, show) {
			var el = overlay.querySelector(selector);
			if (el) el.style.display = show ? '' : 'none';
		}

		function capitalise(str) {
			return str.charAt(0).toUpperCase() + str.slice(1);
		}

		function formatDateRange(start, end) {
			if (!start) return '—';
			var opts = { year: 'numeric', month: 'long', day: 'numeric' };
			var startStr = start.toLocaleDateString('en-NZ', opts);

			// FullCalendar end is exclusive, so subtract one day for display
			if (end) {
				var displayEnd = new Date(end.getTime() - 86400000);
				if (displayEnd.toDateString() !== start.toDateString()) {
					return startStr + ' – ' + displayEnd.toLocaleDateString('en-NZ', opts);
				}
			}
			return startStr;
		}

		// --- Floating notification widget ---
		var notifyWidget   = document.getElementById('inscience-notify-widget');
		var notifyTab      = document.getElementById('inscience-notify-tab');
		var notifyClose    = document.getElementById('inscience-notify-close');
		var SESSION_KEY    = 'inscience_notify_collapsed';

		if (notifyWidget && notifyTab) {
			// Restore collapsed state from sessionStorage
			if (sessionStorage.getItem(SESSION_KEY) === '1') {
				notifyWidget.classList.add('inscience-notify-collapsed');
				notifyTab.setAttribute('aria-expanded', 'false');
			}

			notifyTab.addEventListener('click', function () {
				var isCollapsed = notifyWidget.classList.toggle('inscience-notify-collapsed');
				notifyTab.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
				if (isCollapsed) {
					sessionStorage.setItem(SESSION_KEY, '1');
				} else {
					sessionStorage.removeItem(SESSION_KEY);
				}
			});

			if (notifyClose) {
				notifyClose.addEventListener('click', function () {
					notifyWidget.classList.add('inscience-notify-collapsed');
					notifyTab.setAttribute('aria-expanded', 'false');
					sessionStorage.setItem(SESSION_KEY, '1');
				});
			}
		}
	});
})();
