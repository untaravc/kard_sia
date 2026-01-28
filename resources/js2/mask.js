import Vue from 'vue';

const truncateText = (value, limit = 30) => {
    if (value === null || value === undefined) {
        return '';
    }
    const text = String(value);
    if (text.length <= limit) {
        return text;
    }
    if (limit <= 3) {
        return text.slice(0, limit);
    }
    return `${text.slice(0, limit - 3)}...`;
};

Vue.filter('truncate', truncateText);

export default {
    truncateText,
};
