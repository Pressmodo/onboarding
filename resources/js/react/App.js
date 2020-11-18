import Header from './Header'
import GettingStartedPage from './pages/GettingStarted'

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
					<GettingStartedPage></GettingStartedPage>
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
