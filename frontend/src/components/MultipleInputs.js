import React from "react";
import styled from "styled-components/macro";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faPlusCircle } from '@fortawesome/free-solid-svg-icons'

const InputWrapper = styled.div``

export default class MultipleInputs extends React.Component{
    constructor(props) {
        super(props);
        this.state = {
            multiple: undefined,
            key: 0,
            type: props.type,
            map: props.map,
            multElement: []
        };
    }
    componentDidMount() {
        this.addNewMultiple()
    }
    addNewMultiple() {
        let newEl = [];
        this.state.map.forEach(item => newEl.push(item));
        newEl.push(<FontAwesomeIcon key={'add' + this.state.key} onClick={() => this.addNewMultiple()} icon={faPlusCircle}/>)
        let multElement = this.state.multElement;
        multElement.push(newEl);
        this.setState({multElement: multElement, key: this.state.key++});
    }
    render() {
        return (
            <InputWrapper id={'multiple-' + this.state.type}>
                {this.state.multElement}
            </InputWrapper>
        );
    }
};
