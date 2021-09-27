/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    ['jquery', 'ko'],
    function ($, ko) {
        return {
            ratingData: ko.observable(window.chatboxConfig.agentRating),
            ratingTotal: ko.observable(0),
            totalRating: function () {
                var cumulative = 0;
                var total = _.map(this.ratingData(), function (index) {
                    cumulative += index;
                    return cumulative;
                });
                if (total[4] == 0) {
                    this.ratingTotal(1);
                } else {
                    this.ratingTotal(total[4]);
                }
                return total[4];
            },
            /**
             * Get Percentage
             */
            getPercentage: function (index) {
                this.totalRating();
                var percent = parseInt(this.ratingData()[index]) * 100 / this.ratingTotal();
                return percent;
            },
            /**
             * Get Rating Total Count
             */
            getRatingTotalCount: function (index) {
                return this.ratingData()[index];
            },
            /**
             * Get Average Rating
             */
            getAverageRating: function () {
                this.totalRating();
                var cumulative = 0;
                var max = _.map(this.ratingData(), function (value, index) {
                    cumulative += parseInt(index) * value;
                    return cumulative;
                });
                return (max[4] / this.ratingTotal()).toFixed(2);
            },
            /**
             * Get Average Percentage
             */
            getAveragePercentage: function () {
                this.totalRating();
                var cumulative = 0;
                var max = _.map(this.ratingData(), function (value, index) {
                    cumulative += parseInt(index) * value;
                    return cumulative;
                });
                var averageRating = max[4] / this.ratingTotal();
                var maxRating = this.ratingTotal() * 5;
                var totalRating = averageRating * this.ratingTotal();
                return (totalRating / maxRating) * 100;
            },
            /**
             * Update variable content.
             *
             * @param {Object} updatedChat
             * @returns void
             */
            update: function (updatedChat) {
                if (updatedChat.agentRating) {
                    this.ratingData(updatedChat.agentRating);
                }
            }
        };
    }
);