import React, { Fragment } from 'react';
import { sprintf, __ } from '@wordpress/i18n';
import useRouter from '../hooks/useRouter';
import PmLogo from '../PressmodoLogo';

import {
	EuiPage,
	EuiPageBody,
	EuiPageContent,
	EuiPageContentBody,
	EuiEmptyPrompt,
	EuiButton,
	EuiCallOut,
} from '@elastic/eui';

export default () => {

	const router = useRouter();

	return (
		<EuiPage className="pm-onboarding-page-wrapper">
			<EuiPageBody component="div">
				<EuiPageContent verticalPosition="center" horizontalPosition="center">
					<EuiPageContentBody>

						<PmLogo></PmLogo>

						<EuiEmptyPrompt
							title={<h2> {sprintf('Welcome to %s', pmOnboarding.theme)} </h2>}
							body={
								<Fragment>
									<p>
										{__('This wizard will setup your theme, install plugins, and import content. It is optional & should take only a few minutes.')}
									</p>
									<EuiCallOut title={__('Please note:')} color="warning">
										<p>
											{__('We recommend you backup your website content before attempting a full site import.')}
											<strong> {__('The import will replace your entire site content.')}</strong>
										</p>
										<p>
											{__('Due to copyright restrictions, demo images will not be imported and will be replaced by placeholder images.')}
										</p>
									</EuiCallOut>
								</Fragment>
							}
							actions={
								<EuiButton color="primary" fill onClick={ (e) => router.push('onboarding/upload') }>
									{__('Get started')}
								</EuiButton>
							}
						/>
					</EuiPageContentBody>
				</EuiPageContent>
			</EuiPageBody>

		</EuiPage>
	)
}
