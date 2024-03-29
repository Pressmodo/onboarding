import Header from './Header'
import GettingStartedPage from './pages/GettingStarted'
import UploadPackage from './pages/UploadPackage'
import VerifyPlugins from './pages/VerifyPlugins'
import ImportMedia from './pages/ImportMedia'
import ImportDatabase from './pages/ImportDatabase'

import {
	BrowserRouter as Router,
	Switch,
	Route,
} from "react-router-dom";

function OnboardingApp() {

	return (
		<div className="pm-onboarding-page-wrapper">
			<Switch>
				<Route path="/onboarding/database">
					<ImportDatabase />
				</Route>
				<Route path="/onboarding/media">
					<ImportMedia />
				</Route>
				<Route path="/onboarding/plugins">
					<VerifyPlugins></VerifyPlugins>
				</Route>
				<Route path="/onboarding/upload">
					<UploadPackage></UploadPackage>
				</Route>
				<Route>
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
