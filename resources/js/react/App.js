import Header from './Header'
import OnboardingPage from './OnboardingPage'

import {
	BrowserRouter as Router,
	Switch,
	Route,
} from "react-router-dom";

function OnboardingApp() {

	return (
		<div>
			<Switch>
				<Route path="/onboarding/upload">
					about page
				</Route>
				<Route path="/onboarding">
					<OnboardingPage></OnboardingPage>
				</Route>
			</Switch>
		</div>
	);
}

export default function App() {
	return (
		<div className="pressmdo-onboarding-app">
			<Header></Header>
			<Router>
				<OnboardingApp />
			</Router>
		</div>
	);
}
