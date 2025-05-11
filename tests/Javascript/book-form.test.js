const { Validator } = require('../../assets/js/modules/validator');

describe('Form Validator', () => {
    test('required validation should pass with non-empty string', () => {
        expect(Validator.required('Test')).toBe(true);
    });

    test('required validation should fail with empty string', () => {
        expect(Validator.required('')).toBe('This field is required');
    });

    test('pattern validation should pass with matching string', () => {
        const isbnPattern = /^(?:\d[- ]?){9}[\dXx]$|^(?:\d[- ]?){13}$/;
        expect(Validator.pattern('978-3-16-148410-0', isbnPattern)).toBe(true);
    });

    test('pattern validation should fail with non-matching string', () => {
        const isbnPattern = /^(?:\d[- ]?){9}[\dXx]$|^(?:\d[- ]?){13}$/;
        expect(Validator.pattern('invalid-isbn', isbnPattern)).toBe('Invalid format');
    });

    test('notFutureDate validation should pass with past date', () => {
        const pastDate = new Date();
        pastDate.setFullYear(pastDate.getFullYear() - 1);
        expect(Validator.notFutureDate(pastDate.toISOString())).toBe(true);
    });

    test('notFutureDate validation should fail with future date', () => {
        const futureDate = new Date();
        futureDate.setFullYear(futureDate.getFullYear() + 1);
        expect(Validator.notFutureDate(futureDate.toISOString())).toBe('Date cannot be in the future');
    });
});