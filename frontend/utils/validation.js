class Validation {
    static validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    static validatePassword(password) {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
        return passwordRegex.test(password);
    }

    static validateName(name) {
        return name.length >= 2 && /^[a-zA-Z\s]*$/.test(name);
    }

    static validatePrice(price) {
        return !isNaN(price) && price > 0;
    }

    static validateQuantity(quantity) {
        return Number.isInteger(Number(quantity)) && quantity > 0;
    }

    static validateRequired(value) {
        return value !== null && value !== undefined && value.trim() !== '';
    }

    static sanitizeInput(input) {
        return input.replace(/[<>]/g, '');
    }

    static validateForm(formData, rules) {
        const errors = {};
        
        for (const [field, value] of Object.entries(formData)) {
            if (rules[field]) {
                const fieldRules = rules[field];
                
                if (fieldRules.required && !this.validateRequired(value)) {
                    errors[field] = 'This field is required';
                }
                
                if (fieldRules.email && !this.validateEmail(value)) {
                    errors[field] = 'Invalid email format';
                }
                
                if (fieldRules.password && !this.validatePassword(value)) {
                    errors[field] = 'Password must be at least 8 characters with 1 uppercase, 1 lowercase, and 1 number';
                }
                
                if (fieldRules.name && !this.validateName(value)) {
                    errors[field] = 'Name must be at least 2 characters and contain only letters';
                }
                
                if (fieldRules.price && !this.validatePrice(value)) {
                    errors[field] = 'Price must be a positive number';
                }
                
                if (fieldRules.quantity && !this.validateQuantity(value)) {
                    errors[field] = 'Quantity must be a positive integer';
                }
            }
        }
        
        return {
            isValid: Object.keys(errors).length === 0,
            errors
        };
    }
}

export default Validation; 