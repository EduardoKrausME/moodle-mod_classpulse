// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * dashboard.js
 *
 * @package   mod_classpulse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery", "core/ajax", "core/notification"], function($, Ajax, Notification) {
    "use strict";

    const COLORS = ["#d9534f", "#f0ad4e", "#5bc0de", "#5cb85c"];
    let currentChart = "bar";
    let latestData = null;

    const escapeHtml = function(value) {
        return $("<div>").text(value).html();
    };

    const renderLegend = function(root, items) {
        const legend = root.find('[data-region="legend"]');
        const html = items.map(function(item, index) {
            return '<div class="mod-classpulse-legend-item">' +
                '<span class="mod-classpulse-swatch" style="background:' + COLORS[index] + '"></span>' +
                '<span class="mod-classpulse-legend-label">' + escapeHtml(item.label) + '</span>' +
                '<strong>' + item.count + '</strong>' +
                '<span class="text-muted">(' + item.percentage.toFixed(1) + '%)</span>' +
                '</div>';
        }).join("");
        legend.html(html);
    };

    const renderBar = function(root, items) {
        const max = Math.max.apply(null, items.map(function(item) { return item.count; }).concat([1]));
        const html = '<div class="mod-classpulse-bars">' + items.map(function(item, index) {
            const height = Math.max((item.count / max) * 100, item.count > 0 ? 4 : 0);
            return '<div class="mod-classpulse-bar-column">' +
                '<div class="mod-classpulse-bar-value">' + item.count + '</div>' +
                '<div class="mod-classpulse-bar-track"><div class="mod-classpulse-bar" style="height:' + height + '%;background:' + COLORS[index] + '"></div></div>' +
                '<div class="mod-classpulse-axis-label">' + escapeHtml(item.shortlabel) + '</div>' +
                '</div>';
        }).join("") + '</div>';
        root.find('[data-region="chart"]').html(html);
    };

    const renderPie = function(root, items, total) {
        const chart = root.find('[data-region="chart"]');
        if (total === 0) {
            chart.html('<div class="mod-classpulse-empty">0</div>');
            return;
        }

        let start = 0;
        const cx = 120;
        const cy = 120;
        const radius = 95;
        const paths = items.map(function(item, index) {
            const fraction = item.count / total;
            const end = start + (fraction * Math.PI * 2);
            if (fraction === 0) {
                return "";
            }
            if (fraction >= 0.999999) {
                start = end;
                return '<circle cx="120" cy="120" r="95" fill="' + COLORS[index] + '"></circle>';
            }
            const x1 = cx + radius * Math.cos(start - Math.PI / 2);
            const y1 = cy + radius * Math.sin(start - Math.PI / 2);
            const x2 = cx + radius * Math.cos(end - Math.PI / 2);
            const y2 = cy + radius * Math.sin(end - Math.PI / 2);
            const largeArc = fraction > 0.5 ? 1 : 0;
            const path = 'M ' + cx + ' ' + cy + ' L ' + x1 + ' ' + y1 + ' A ' + radius + ' ' + radius + ' 0 ' + largeArc + ' 1 ' + x2 + ' ' + y2 + ' Z';
            start = end;
            return '<path d="' + path + '" fill="' + COLORS[index] + '"></path>';
        }).join("");

        chart.html('<div class="mod-classpulse-pie-wrap"><svg class="mod-classpulse-pie" viewBox="0 0 240 240" role="img" aria-label="' + total + '">' + paths +
            '<circle cx="120" cy="120" r="52" class="mod-classpulse-pie-hole"></circle>' +
            '<text x="120" y="116" text-anchor="middle" class="mod-classpulse-pie-total">' + total + '</text>' +
            '<text x="120" y="140" text-anchor="middle" class="mod-classpulse-pie-caption">' + escapeHtml(latestData.responseslabel) + '</text>' +
            '</svg></div>');
    };

    const renderLine = function(root, items) {
        const max = Math.max.apply(null, items.map(function(item) { return item.count; }).concat([1]));
        const width = 640;
        const height = 280;
        const paddingX = 60;
        const paddingY = 35;
        const usableWidth = width - paddingX * 2;
        const usableHeight = height - paddingY * 2 - 35;
        const points = items.map(function(item, index) {
            const x = paddingX + (usableWidth / (items.length - 1)) * index;
            const y = paddingY + usableHeight - (item.count / max) * usableHeight;
            return {x: x, y: y, item: item, index: index};
        });
        const polyline = points.map(function(point) { return point.x + ',' + point.y; }).join(' ');
        const circles = points.map(function(point) {
            return '<circle cx="' + point.x + '" cy="' + point.y + '" r="6" fill="' + COLORS[point.index] + '"></circle>' +
                '<text x="' + point.x + '" y="' + (point.y - 12) + '" text-anchor="middle" class="mod-classpulse-line-value">' + point.item.count + '</text>' +
                '<text x="' + point.x + '" y="' + (height - 12) + '" text-anchor="middle" class="mod-classpulse-line-label">' + escapeHtml(point.item.shortlabel) + '</text>';
        }).join("");
        const svg = '<svg class="mod-classpulse-line" viewBox="0 0 ' + width + ' ' + height + '" role="img">' +
            '<line x1="' + paddingX + '" y1="' + (paddingY + usableHeight) + '" x2="' + (width - paddingX) + '" y2="' + (paddingY + usableHeight) + '" class="mod-classpulse-grid"></line>' +
            '<polyline points="' + polyline + '" class="mod-classpulse-line-path"></polyline>' + circles + '</svg>';
        root.find('[data-region="chart"]').html(svg);
    };

    const render = function(root, data) {
        root.find('[data-region="total"]').text(data.total);
        root.find('[data-region="updated"]').text(data.updated);
        renderLegend(root, data.items);

        if (currentChart === "pie") {
            renderPie(root, data.items, data.total);
        } else if (currentChart === "line") {
            renderLine(root, data.items);
        } else {
            renderBar(root, data.items);
        }

        root.find('[data-chart]').removeClass('active').attr('aria-pressed', 'false');
        root.find('[data-chart="' + currentChart + '"]').addClass('active').attr('aria-pressed', 'true');
    };

    const load = function(root) {
        const cmid = parseInt(root.data("cmid"), 10);
        Ajax.call([{
            methodname: "mod_classpulse_get_results",
            args: {cmid: cmid},
        }])[0].done(function(data) {
            latestData = data;
            render(root, data);
        }).fail(Notification.exception);
    };

    const init = function() {
        const root = $('[data-region="classpulse-dashboard"]');
        if (!root.length) {
            return;
        }
        currentChart = root.data("default-chart") || "bar";
        root.on("click", "[data-chart]", function() {
            currentChart = $(this).data("chart");
            if (latestData) {
                render(root, latestData);
            }
        });
        load(root);
        window.setInterval(function() {
            if (document.visibilityState === "visible") {
                load(root);
            }
        }, 2500);
    };

    return {init: init};
});
