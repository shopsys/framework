import { parseNumber } from '../utils/number';

(function (window) {

    const SymfonyComponentValidatorConstraintNotIdenticalTo = function () {
        this.message = '';
        this.value = null;

        this.validate = function (value) {

            const f = FpJsFormValidator;
            let compareValue = value;
            if (parseNumber(this.value) !== null) {
                compareValue = parseNumber(value);
            }

            if (f.isValueEmty(value) || (compareValue !== null && compareValue !== this.value)) {
                return [];
            } else {
                return [
                    this.message
                        .replace('{{ value }}', String(value))
                        .replace('{{ compared_value }}', String(this.value))
                ];
            }
        };
    };

    window.SymfonyComponentValidatorConstraintNotIdenticalTo = SymfonyComponentValidatorConstraintNotIdenticalTo;

})(window);
