import React from "react";
import styled from "styled-components/macro";
import  createTableFromData from  "../constants/DataTable";
const QuestionsConstant = require('../constants/questions');
const API = require("../API/backendAPI");

const LeftDiv = styled.div``;
const Button = styled.button``;

class Questions extends React.Component{
    constructor(props) {
        super(props);
        this.state = {
            questionBox: undefined
        }
    }
    queryDataFromSelect = (item) => {
        API.getRequest(item, 'get_question', 'question')
            .then(res=>createTableFromData(res))
            .then(element => this.setState({questionBox: element}))
            .catch(err=>{
                if (err.error === 'No data') {
                    let errorEl = (
                        <div>
                            {err.error}
                        </div>
                    );
                    this.setState({questionBox: errorEl});
                    console.log(this.state.questionBox);
                }
                console.error(err);
            });
    }

    render() {
        let keyInt = 0;
        let btns = QuestionsConstant.questions.map((item) => {
            return (
                <Button key={"qbtn" + keyInt++} onClick={() => this.queryDataFromSelect(item.name)}>
                    {item.name}
                </Button>
            )
        });
        console.log(btns);
        return (
            <div key={'main-div-questions'}>
                <h1>Questions</h1>
                <LeftDiv id="question-buttons-left" key={'left-body'}>
                    {btns}
                </LeftDiv>
                <div id="questions-box">{this.state.questionBox}</div>
            </div>
        );
    }
}

export default Questions;