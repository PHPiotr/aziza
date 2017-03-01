var Contact = React.createClass({
    getInitialState: function() {
        return {
            phone: '',
            email: '',
            message: '',
            phoneError: '',
            emailError: '',
            messageError: '',
            headerText: 'Dowiedz się więcej',
            submitDisabled: false
        };
    },
    onChangeHandler: function(event) {

        var inputValue, inputName, popoverTrigger;

        inputValue = event.target.value;
        inputName = event.target.name;
        popoverTrigger = $('.' + inputName + '-popover');

        this.setState({[inputName]: inputValue});

        if (popoverTrigger.next('.popover').length > 0) {
            this.setState({[inputName + 'Error']: ''});
            popoverTrigger.popover('hide');
        }
    },
    onSubmitHandler: function(event) {

        var self = this;
        var defaultHeaderText = self.state.headerText;

        $.ajax({
            url: 'kontakt/wyslij',
            type: 'post',
            dataType: 'json',
            data: {
                phone: self.state.phone,
                email: self.state.email,
                message: self.state.message
            },
            beforeSend: function() {
                self.setState({
                    submitDisabled: true,
                    headerText: 'Wysyłanie...'
                });
            },
            success: function(data) {

                var errors;

                if (data.ok) {
                    self.setState({
                        headerText: data.msg,
                        phone: '',
                        email: '',
                        message: ''
                    });

                    return;
                }

                errors = data.errors || [];

                self.setState({headerText: defaultHeaderText});

                for (var key in errors) {
                    if (errors.hasOwnProperty(key)) {
                        self.setState({[key + 'Error']: errors[key]});
                        $('.' + key + '-popover').popover('show');
                    }
                }
            },
            complete: function() {
                self.setState({submitDisabled: false});
                if (self.state.headerText !== defaultHeaderText) {
                    setTimeout(function() {
                        self.setState({headerText: defaultHeaderText});
                    }, 3000);
                }
            }
        });
        event.preventDefault();
    },
    render: function() {
        return (
                React.createElement('div', {className: 'row contact'},
                        React.createElement('div', {className: 'col-sm-offset-4 col-sm-8 head'},
                                React.createElement('h1', null, this.state.headerText)
                                ),
                        React.createElement('form', {onSubmit: this.onSubmitHandler, className: 'form-horizontal'},
                                React.createElement('div', {className: 'form-group'},
                                        React.createElement('label', {htmlFor: 'phone', className: 'col-sm-offset-4 col-sm-1 control-label'}, 'Telefon'),
                                        React.createElement('div', {className: 'col-sm-6'},
                                                React.createElement('input', {'data-placement': 'top', 'data-content': this.state.phoneError, onChange: this.onChangeHandler, value: this.state.phone, type: 'text', maxLength: '15', className: 'phone-popover form-control', id: 'phone', name: 'phone'})
                                                )
                                        ),
                                React.createElement('div', {className: 'form-group'},
                                        React.createElement('label', {htmlFor: 'email', className: 'col-sm-offset-4 col-sm-1 control-label'}, 'Email'),
                                        React.createElement('div', {className: 'col-sm-6'},
                                                React.createElement('input', {'data-placement': 'top', 'data-content': this.state.emailError, onChange: this.onChangeHandler, value: this.state.email, type: 'text', className: 'email-popover form-control', id: 'email', name: 'email'})
                                                )
                                        ),
                                React.createElement('div', {className: 'form-group'},
                                        React.createElement('div', {className: 'col-sm-offset-4 col-sm-7'},
                                                React.createElement('textarea', {'data-placement': 'top', 'data-content': this.state.messageError, onChange: this.onChangeHandler, value: this.state.message, className: 'message-popover form-control', rows: 6, id: 'message', name: 'message'})
                                                )
                                        ),
                                React.createElement('div', {className: 'form-group submit'},
                                        React.createElement('div', {className: 'col-sm-offset-9 col-sm-2'},
                                                React.createElement('input', {disabled: this.state.submitDisabled, type: 'submit', className: 'form-control', value: 'Wyślij'})
                                                )
                                        )
                                )
                        )
                );
    }
});


ReactDOM.render(React.createElement(Contact), document.getElementById('contact'));