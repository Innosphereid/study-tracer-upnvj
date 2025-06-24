/**
 * Combined actions for the questionnaire store
 * Aggregates all actions from different modules
 *
 * @module questionnaire/store/actions
 */

import CoreActions from "./core";
import SectionActions from "./sections";
import QuestionActions from "./questions";
import UIActions from "./ui";
import PublishingActions from "./publishing";

/**
 * Combine all actions into a single object
 * @type {Object}
 */
export default {
    ...CoreActions,
    ...SectionActions,
    ...QuestionActions,
    ...UIActions,
    ...PublishingActions,
};
