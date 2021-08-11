import React from "react";
import { Route } from 'react-router-dom';
import Header from "./components/Header";
import Create from "./components/Create";
import Home from "./components/Home";
import PreloadedQueries from "./components/PreloadedQueries";
import Display from "./components/Display";
import Delete from "./components/Delete";
import Edit from "./components/Edit";


class App extends React.Component {
	constructor(props) {
		super(props);
		this.state = { current:  window.location.pathname };
		this.links = [
			{
				link: "/",
				name: "Home",
				component: Home
			},
			{
				link: "/create",
				name: "Create",
				component: Create
			},
			{
				link: "/edit",
				name: "Edit",
				component: Edit
			},
			{
				link: "/display",
				name: "Display",
				component: Display
			},
			{
				link: "/delete",
				name: "Delete",
				component: Delete
			},
			{
				link: "/preloaded-queries",
				name: "Preloaded Queries",
				component: PreloadedQueries
			}
		]
	}
	handleNavClick = (event, data) => {
        this.setState({current: data});
    }

	render() {
		let i = 0;
		let routes = this.links.map(({link, component}) => {
			if (link==="/") {
				return <Route key={i++} exact path={link} component={component}/>
			}
			return <Route key={i++} path={link} component={component}/>
		})
		return (
			<div className="App">
				<Header links={this.links} handleClick={this.handleNavClick} current={this.state.current} keys={i}/>
				{routes}
			</div>
		);
	}
}

export default App;