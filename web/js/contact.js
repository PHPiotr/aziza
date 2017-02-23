var Contact = React.createClass({
    getInitialState: function () {
        return {
            phone: '',
            email: '',
            message: ''
        };
    },
    onChangeHandler: function(event) {
        var inputValue, inputName;
        inputValue = event.target.value;
        inputName = event.target.name;
        this.setState({[inputName]: inputValue});
    },
    onSubmitHandler: function(event) {
        console.log(this.state);
        // TODO: Send email
        event.preventDefault();
    },
    render: function () {
        return React.createElement('form', {onSubmit: this.onSubmitHandler, className: 'form-horizontal'},
                React.createElement('div', {className: 'form-group'},
                        React.createElement('label', {htmlFor: 'phone', className: 'col-sm-offset-4 col-sm-1 control-label'}, 'Telefon'),
                        React.createElement('div', {className: 'col-sm-6'},
                                React.createElement('input', {onChange: this.onChangeHandler, value: this.state.phone, type: 'text', className: 'form-control', id: 'phone', name: 'phone', placeholder: 'zadzwonimy do Ciebie'})
                                )
                        ),
                React.createElement('div', {className: 'form-group'},
                        React.createElement('label', {htmlFor: 'email', className: 'col-sm-offset-4 col-sm-1 control-label'}, 'Email'),
                        React.createElement('div', {className: 'col-sm-6'},
                                React.createElement('input', {onChange: this.onChangeHandler, value: this.state.email, type: 'text', className: 'form-control', id: 'email', name: 'email', placeholder: 'napiszemy do Ciebie'})
                                )
                        ),
                React.createElement('div', {className: 'form-group'},
                        React.createElement('div', {className: 'col-sm-offset-4 col-sm-7'},
                                React.createElement('textarea', {onChange: this.onChangeHandler, value: this.state.message, className: 'form-control', rows: 6, name: 'message'})
                                )
                        ),
                React.createElement('div', {className: 'form-group submit'},
                        React.createElement('div', {className: 'col-sm-offset-9 col-sm-2'},
                                React.createElement('input', {type: 'submit', className: 'form-control', value: 'Wyślij'})
                                )
                        )
                );
    }
});


ReactDOM.render(React.createElement(Contact), document.getElementById('contact'));