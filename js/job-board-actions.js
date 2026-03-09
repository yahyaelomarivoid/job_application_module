/**
 * Drupal behavior: hide Accept/Reject buttons after one is clicked.
 * Uses the Drupal 10 `once()` API (core/once) instead of the
 * removed jQuery $.once() plugin.
 */
(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.jobBoardActions = {
    attach: function (context) {
      // `once()` ensures this runs only once per element even if
      // Drupal.attachBehaviors() is called multiple times (e.g. after AJAX).
      once('job-board-actions', '.job-board-table td:last-child a', context)
        .forEach(function (link) {
          link.addEventListener('click', function () {
            // Find the <td> that contains both action links.
            var actionCell = link.closest('td');

            // Hide all anchor tags inside that cell immediately.
            actionCell.querySelectorAll('a').forEach(function (a) {
              a.style.display = 'none';
            });

            // Replace them with a neutral "Processing..." label.
            var label = document.createElement('em');
            label.textContent = 'Processing\u2026';
            actionCell.appendChild(label);
          });
        });
    }
  };

})(Drupal, once);
