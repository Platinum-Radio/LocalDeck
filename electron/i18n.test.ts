import{describe,expect,it}from'vitest';
import{localeFor,translateText}from'../src/i18n';

describe('LocalDeck i18n',()=>{
 it('vertaalt vaste en dynamische dashboardtekst en houdt Nederlands intact',()=>{expect(translateText('Instellingen','en')).toBe('Settings');expect(translateText('3 services actief','en')).toBe('3 services running');expect(translateText('Instellingen','nl')).toBe('Instellingen');expect(localeFor('en')).toBe('en-US')});
 it('vertaalt de nieuwe instellingen- en systeemvakuitleg',()=>{expect(translateText('Instellingen zonder eindeloos scrollen','en')).toBe('Settings without endless scrolling');expect(translateText('Rode kruis: LocalDeck blijft actief','en')).toBe('Red close button: LocalDeck stays active');expect(translateText('Website, documentatie en downloads','en')).toBe('Website, documentation and downloads')});
});
