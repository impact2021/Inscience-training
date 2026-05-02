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
			currentEvent = event; // capture for Add-to-Calendar
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
			// Close add-to-calendar dropdown if open
			if (addCalDropdown && !addCalDropdown.hidden) {
				addCalDropdown.hidden = true;
				if (addCalToggle) addCalToggle.setAttribute('aria-expanded', 'false');
			}
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

		// --- Add-to-Calendar ---
		var addCalToggle   = document.getElementById('inscience-addcal-toggle');
		var addCalDropdown = document.getElementById('inscience-addcal-dropdown');
		var addCalGoogle   = document.getElementById('inscience-addcal-google');
		var addCalIcs      = document.getElementById('inscience-addcal-ics');
		var currentEvent   = null; // set when modal opens

		if (addCalToggle && addCalDropdown) {
			addCalToggle.addEventListener('click', function (e) {
				e.stopPropagation();
				var open = addCalDropdown.hidden;
				addCalDropdown.hidden = !open;
				addCalToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			});

			// Close dropdown when clicking outside
			document.addEventListener('click', function () {
				if (!addCalDropdown.hidden) {
					addCalDropdown.hidden = true;
					addCalToggle.setAttribute('aria-expanded', 'false');
				}
			});

			addCalDropdown.addEventListener('click', function (e) {
				e.stopPropagation();
			});
		}

		if (addCalGoogle) {
			addCalGoogle.addEventListener('click', function (e) {
				e.preventDefault();
				if (!currentEvent) return;
				var url = buildGoogleCalUrl(currentEvent);
				if (url) window.open(url, '_blank', 'noopener');
				addCalDropdown.hidden = true;
				addCalToggle.setAttribute('aria-expanded', 'false');
			});
		}

		if (addCalIcs) {
			addCalIcs.addEventListener('click', function (e) {
				e.preventDefault();
				if (!currentEvent) return;
				downloadIcs(currentEvent);
				addCalDropdown.hidden = true;
				addCalToggle.setAttribute('aria-expanded', 'false');
			});
		}

		function buildGoogleCalUrl(event) {
			var props     = event.extendedProps || {};
			var startDate = fmtDate(event.start);
			// FullCalendar end is exclusive – same convention Google uses for all-day
			var endDate   = event.end ? fmtDate(event.end) : fmtDate(nextDay(event.start));
			var details   = props.description || '';
			if (props.price)    details += (details ? '\n' : '') + 'Price: NZ$' + props.price;
			if (props.us_codes) details += (details ? '\n' : '') + 'Unit Standards: ' + props.us_codes;

			return 'https://www.google.com/calendar/render?action=TEMPLATE' +
				'&text='     + encodeURIComponent(event.title) +
				'&dates='    + startDate + '/' + endDate +
				'&details='  + encodeURIComponent(details) +
				'&location=' + encodeURIComponent(props.location || '');
		}

		function downloadIcs(event) {
			var props     = event.extendedProps || {};
			var startDate = fmtDate(event.start);
			var endDate   = event.end ? fmtDate(event.end) : fmtDate(nextDay(event.start));
			var details   = props.description || '';
			if (props.price)    details += (details ? '\\n' : '') + 'Price: NZ$' + props.price;
			if (props.us_codes) details += (details ? '\\n' : '') + 'Unit Standards: ' + props.us_codes;

			var uid = 'inscience-' + (event.id || Date.now()) + '@inscience.co.nz';
			var now = new Date();
			var stamp = now.toISOString().replace(/[-:.]/g, '').slice(0, 15) + 'Z';

			var lines = [
				'BEGIN:VCALENDAR',
				'VERSION:2.0',
				'PRODID:-//InScience Training//EN',
				'CALSCALE:GREGORIAN',
				'BEGIN:VEVENT',
				'UID:'         + uid,
				'DTSTAMP:'     + stamp,
				'DTSTART;VALUE=DATE:' + startDate,
				'DTEND;VALUE=DATE:'   + endDate,
				'SUMMARY:'     + icsEscape(event.title),
				'DESCRIPTION:' + icsEscape(details),
				'LOCATION:'    + icsEscape(props.location || ''),
				'END:VEVENT',
				'END:VCALENDAR',
			];

			var blob = new Blob([lines.join('\r\n')], { type: 'text/calendar;charset=utf-8' });
			var url  = URL.createObjectURL(blob);
			var a    = document.createElement('a');
			a.href   = url;
			a.download = sanitiseFilename(event.title) + '.ics';
			document.body.appendChild(a);
			a.click();
			setTimeout(function () {
				document.body.removeChild(a);
				URL.revokeObjectURL(url);
			}, 100);
		}

		/** Format a Date object as YYYYMMDD */
		function fmtDate(d) {
			if (!d) return '';
			var y = d.getFullYear();
			var m = String(d.getMonth() + 1).padStart(2, '0');
			var day = String(d.getDate()).padStart(2, '0');
			return y + m + day;
		}

		function nextDay(d) {
			var nd = new Date(d);
			nd.setDate(nd.getDate() + 1);
			return nd;
		}

		function icsEscape(str) {
			return (str || '').replace(/\\/g, '\\\\').replace(/;/g, '\\;').replace(/,/g, '\\,').replace(/\n/g, '\\n');
		}

		function sanitiseFilename(str) {
			return (str || 'event').replace(/[^a-z0-9\-_ ]/gi, '-').trim().replace(/\s+/g, '-').slice(0, 60);
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
