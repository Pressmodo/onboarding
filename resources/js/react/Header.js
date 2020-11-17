import { __ } from '@wordpress/i18n';

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faLifeRing, faPaperPlane } from '@fortawesome/free-solid-svg-icons'

import {
	EuiHeader,
	EuiHeaderLogo,
	EuiHeaderSectionItem,
	EuiToolTip,
	EuiHeaderLinks,
	EuiHeaderLink,
} from '@elastic/eui';

export default () => {

	const wpLogo = pmOnboarding.plugin_url + 'resources/images/wp-logo.svg';

	return (
		<EuiHeader position="fixed">
			<EuiHeaderSectionItem className="pm-onboarding-header">
				<EuiToolTip content={ __( 'Back to the WordPress dashboard' ) }>
					<EuiHeaderLogo
						iconType={ wpLogo }
						href={ pmOnboarding.admin_url }
						className= 'wp-logo'
						aria-label={ __( 'Back to the WordPress dashboard' ) }
					/>
				</EuiToolTip>
			</EuiHeaderSectionItem>
			<EuiHeaderSectionItem className="pm-onboarding-support-links">
				<EuiHeaderLinks>
					<EuiHeaderLink href={ pmOnboarding.documentation_url }>
						<FontAwesomeIcon icon={ faLifeRing } />
						{ __( 'Documentation' ) }
					</EuiHeaderLink>
					<EuiHeaderLink href={ pmOnboarding.support_url }>
						<FontAwesomeIcon icon={ faPaperPlane } />
						{ __( 'Support' ) }
					</EuiHeaderLink>
				</EuiHeaderLinks>
			</EuiHeaderSectionItem>
		</EuiHeader>
	)
}
