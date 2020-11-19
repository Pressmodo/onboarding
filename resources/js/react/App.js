import Header from './Header'
import GettingStartedPage from './pages/GettingStarted'
import UploadPackage from './pages/UploadPackage'
import VerifyPlugins from './pages/VerifyPlugins'

import {
	BrowserRouter as Router,
	Switch,
	Route,
} from "react-router-dom";

function OnboardingApp() {
	return (
		<div className="pm-onboarding-page-wrapper">
			<Switch>
				<Route path="/onboarding/plugins">
					<VerifyPlugins></VerifyPlugins>
				</Route>
				<Route path="/onboarding/upload">
					<UploadPackage></UploadPackage>
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
